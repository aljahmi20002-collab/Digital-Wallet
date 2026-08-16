<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\MethodType;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentLink\PayPaymentLinkRequest;
use App\Models\DepositMethod;
use App\Models\PaymentLink;
use App\Services\PaymentLinkService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Payment;

class PaymentLinkCheckoutController extends Controller
{
    public function __construct(protected PaymentLinkService $paymentLinks) {}

    /**
     * Public landing page for a payment link.
     */
    public function show(string $token): View
    {
        try {
            $paymentLink = $this->paymentLinks->resolvePayableByToken($token);
        } catch (NotifyErrorException $exception) {
            return view('general.payment_link.unavailable', [
                'message' => $exception->getMessage(),
            ]);
        }

        return view('general.payment_link.checkout', [
            'paymentLink'    => $paymentLink,
            'paymentMethods' => $this->availableGatewayMethods($paymentLink),
        ]);
    }

    /**
     * Public payment endpoint — currently supports wallet payment via
     * (wallet_id + Wallet PIN) for guest payers, or the authenticated
     * user's own wallet via use_account=1 + Wallet PIN.
     *
     * Defence-in-depth:
     *   - Route-level `throttle:10,1` middleware (coarse per-IP rate limit).
     *   - Per-(IP + wallet/user) RateLimiter on PIN failures, to
     *     stop bruteforce on a known wallet uuid or account.
     *   - Hardened service checks (account active + email verified, generic
     *     error messages to prevent enumeration).
     *
     * @throws NotifyErrorException
     */
    public function pay(PayPaymentLinkRequest $request, string $token): RedirectResponse|Response
    {
        $paymentLink = $this->paymentLinks->resolvePayableByToken($token);

        $amount   = (float) $request->validated('amount');
        $customer = [
            'name'  => $request->validated('customer_name'),
            'email' => $request->validated('customer_email'),
        ];

        $selectedMethod = (string) $request->validated('selected_method');

        if ($selectedMethod !== MethodType::SYSTEM->value) {
            return $this->payWithGateway($paymentLink, $selectedMethod, $amount, $customer);
        }

        if ($request->boolean('use_account')) {
            if (! auth()->check()) {
                throw new NotifyErrorException(__('Please log in to pay with your account wallet.'));
            }

            $user = $request->user();
            $key  = $this->payThrottleKey($request, 'user:'.$user->id);

            $this->guardPaymentAttempts($key);

            try {
                $this->paymentLinks->payWithWalletUsingPin(
                    $paymentLink,
                    $user,
                    (string) $request->validated('pin'),
                    $amount,
                    $customer
                );
            } catch (NotifyErrorException $e) {
                RateLimiter::hit($key, $this->paymentAttemptDecaySeconds());

                throw $e;
            }

            RateLimiter::clear($key);
        } else {
            $walletId = (string) $request->validated('wallet_id');
            $pin      = (string) $request->validated('pin');
            $key      = $this->payThrottleKey($request, $walletId);

            $this->guardPaymentAttempts($key);

            try {
                $this->paymentLinks->payWithWalletPinCredentials(
                    $paymentLink,
                    $walletId,
                    $pin,
                    $amount,
                    $customer
                );
            } catch (NotifyErrorException $e) {
                RateLimiter::hit($key, $this->paymentAttemptDecaySeconds());

                throw $e;
            }

            RateLimiter::clear($key);
        }

        notifyEvs('success', __('Payment completed successfully.'));

        return redirect()->route('payment-link.success', ['token' => $paymentLink->token]);
    }

    /**
     * Refuse further payment attempts once the throttle key threshold is hit.
     *
     * @throws NotifyErrorException
     */
    protected function guardPaymentAttempts(string $key): void
    {
        if (! RateLimiter::tooManyAttempts($key, $this->paymentAttemptLimit())) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw new NotifyErrorException(
            __('Too many failed attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
            429
        );
    }

    /**
     * Throttle key for guest-credential payment attempts. Scopes by IP and
     * the lower-cased, transliterated wallet id so brute-force on one
     * wallet doesn't interfere with another, while a single attacker IP
     * still gets blocked across whatever wallet ids it tries.
     */
    protected function payThrottleKey(Request $request, string $walletId): string
    {
        return 'payment-link-pay|'.$request->ip().'|'.Str::transliterate(Str::lower($walletId));
    }

    protected function paymentAttemptLimit(): int
    {
        return max(3, min(10, (int) config('security.wallet_pin_attempt_limit', 5)));
    }

    protected function paymentAttemptDecaySeconds(): int
    {
        return max(1, min(60, (int) config('security.wallet_pin_lock_minutes', 15))) * 60;
    }

    /**
     * Simple post-payment confirmation page.
     */
    public function success(string $token): View
    {
        $paymentLink = PaymentLink::query()->byToken($token)->firstOrFail();

        return view('general.payment_link.success', [
            'paymentLink' => $paymentLink,
        ]);
    }

    /**
     * Active automatic gateways that can receive the payment link currency.
     */
    protected function availableGatewayMethods(PaymentLink $paymentLink): Collection
    {
        $paymentLink->loadMissing('merchant.paymentMethods');

        $methodsQuery = DepositMethod::query()
            ->where('type', MethodType::AUTOMATIC)
            ->where('status', true)
            ->where('currency', $paymentLink->currencyCode());

        $merchant = $paymentLink->merchant;

        if ($merchant && $merchant->paymentMethods->isNotEmpty()) {
            $methodIds = $merchant->paymentMethods
                ->filter(fn (DepositMethod $method): bool => $method->type === MethodType::AUTOMATIC
                    && (bool) $method->status
                    && strtoupper((string) $method->currency) === strtoupper($paymentLink->currencyCode()))
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->values()
                ->all();

            if ($methodIds === []) {
                return new Collection;
            }

            $methodsQuery->whereIn('id', $methodIds);
        }

        return $methodsQuery->get();
    }

    /**
     * Create a pending receiver transaction and redirect the payer to the
     * selected external gateway.
     *
     * @throws NotifyErrorException
     */
    protected function payWithGateway(PaymentLink $paymentLink, string $selectedMethod, float $amount, array $customer): RedirectResponse|Response
    {
        $paymentMethod = $this->availableGatewayMethods($paymentLink)
            ->firstWhere('method_code', $selectedMethod);

        if (! $paymentMethod) {
            throw new NotifyErrorException(__('Selected payment gateway is not available for this currency.'));
        }

        $transaction = $this->paymentLinks->createPendingReceiverTransaction($paymentLink, $amount, $customer);
        $transaction->update([
            'provider'    => $paymentMethod->name,
            'description' => __('Payment link payment via :provider', ['provider' => $paymentMethod->name]),
        ]);

        $redirectUrl = Payment::paymentWithPaymentMethod($selectedMethod, $transaction);

        if (filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            return redirect()->away($redirectUrl);
        }

        return response($redirectUrl, 200)->header('Content-Type', 'text/html');
    }
}
