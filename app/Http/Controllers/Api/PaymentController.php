<?php

namespace App\Http\Controllers\Api;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\EnvironmentMode;
use App\Enums\MethodType;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\PaymentInitiateRequest;
use App\Models\Currency as CurrencyModel;
use App\Models\DepositMethod;
use App\Models\Merchant;
use App\Services\MerchantService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Transaction;
use Wallet;

class PaymentController extends Controller
{
    public function initiatePayment(PaymentInitiateRequest $request): JsonResponse
    {
        $validated   = $request->validated();
        $merchant    = $request->merchant; // Ensure merchant is being provided (e.g., via middleware or route model binding)
        $environment = EnvironmentMode::from($request->get('environment'));
        $isSandbox   = $environment->isSandbox();

        $merchant->loadMissing(['user', 'currency', 'supportedCurrencies']);

        $paymentCurrency = CurrencyModel::query()
            ->where('code', strtoupper(trim((string) $validated['currency_code'])))
            ->first();

        if (! $paymentCurrency) {
            return response()->json(['error' => 'Invalid currency code.'], 422);
        }

        if (! $merchant->supportsCurrency((int) $paymentCurrency->id)) {
            return response()->json(['error' => 'Currency is not enabled for this merchant.'], 422);
        }

        // For sandbox mode, generate test credentials if not exists
        if ($isSandbox && ! $merchant->hasTestCredentials()) {
            $merchant->generateTestCredentials();
        }

        // Normalize optional allow_payment_methods (string or array)
        $allowedMethods   = $this->normalizeAllowPaymentMethods($request->input('allow_payment_methods'));
        $gatewaySelection = $this->merchantPaymentMethodSelection($merchant, $paymentCurrency->code);

        // Prepare payment data with environment context
        $paymentData = array_merge(
            $request->only([
                'ref_trx',
                'description',
                'ipn_url',
                'cancel_redirect',
                'success_redirect',
                'customer_name',
                'customer_email',
            ]),
            [
                'merchant_id'   => $merchant->id,
                'merchant_name' => $merchant->business_name,
                'amount'        => $validated['payment_amount'],
                'currency_code' => $paymentCurrency->code,
                'environment'   => $environment->value,
                'is_sandbox'    => $isSandbox,
                // New: Restrict visible checkout methods by DepositMethod name keywords
                'allow_payment_methods'               => $allowedMethods,
                'merchant_payment_methods_restricted' => $gatewaySelection['restricted'],
                'merchant_payment_method_ids'         => $gatewaySelection['ids'],
                'merchant_payment_method_codes'       => $gatewaySelection['codes'],
            ]
        );

        // Calculate fee and net amounts.
        $calculation = $this->calculatePaymentAmounts((float) $validated['payment_amount'], $merchant->fee);

        try {
            DB::beginTransaction();

            $merchantWallet = Wallet::getWalletByUserId($merchant->user->id, $paymentCurrency->code);

            if (! $merchantWallet) {
                DB::rollBack();

                return response()->json(['error' => 'Receiver wallet for this currency is not available.'], 422);
            }

            // Create transaction with environment context
            $transaction = Transaction::create(new TransactionData(
                user_id: $merchant->user->id,
                trx_type: TrxType::RECEIVE_PAYMENT,
                amount: $calculation['amount'],
                amount_flow: AmountFlow::PLUS,
                fee: $calculation['fee'],
                currency: $paymentCurrency->code,
                net_amount: $calculation['net_amount'],
                payable_amount: $validated['payment_amount'],
                payable_currency: $paymentCurrency->code,
                wallet_reference: $merchantWallet->uuid,
                trx_data: $paymentData,
                description: $paymentData['description'] ?? __('Payment from :customer', ['customer' => $paymentData['customer_name']]),
                status: TrxStatus::PENDING
            ));

            // Add sandbox flag to transaction for easy identification
            if ($isSandbox) {
                $transaction->remarks = 'SANDBOX_TRANSACTION';
                $transaction->save();
            }

            DB::commit();

            // Encrypt transaction ID
            $encryptedTrxId = Crypt::encryptString($transaction->trx_id);

            // Generate Laravel Signed URL with token (No custom hash needed)
            $paymentUrl = URL::signedRoute('payment.checkout', [
                'token' => $encryptedTrxId,
            ], now()->addMinutes(900)); // URL expires in 900 minutes

            return response()->json([
                'payment_url' => $paymentUrl,
                'info'        => $paymentData,
            ]);
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Optionally log the exception here before returning a response.
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Calculates the payment amounts including fee deduction.
     */
    protected function calculatePaymentAmounts(float $amount, float $merchantFee): array
    {
        $fee       = $amount * $merchantFee / 100;
        $netAmount = $amount - $fee;

        return [
            'fee'        => $fee,
            'amount'     => $netAmount,
            'net_amount' => $netAmount,
        ];
    }

    public function verifyPayment(Request $request, string $trxId): JsonResponse
    {
        // = request('trx_id');
        if (! $trxId) {
            return response()->json(['error' => 'Transaction ID is required.'], 422);
        }

        $merchant    = $request->merchant;
        $transaction = Transaction::findTransaction($trxId);

        if (! $transaction) {
            return response()->json(['error' => 'Transaction not found.'], 404);
        }

        $trxData = $transaction->trx_data ?? [];
        if ($transaction->trx_type !== TrxType::RECEIVE_PAYMENT || (int) ($trxData['merchant_id'] ?? 0) !== (int) $merchant->id) {
            return response()->json(['error' => 'Transaction not found.'], 404);
        }

        if (($trxData['environment'] ?? null) !== null && $trxData['environment'] !== $request->get('environment')) {
            return response()->json(['error' => 'Transaction not found.'], 404);
        }

        // Use strict enum comparison for status
        return match ($transaction->status) {
            TrxStatus::COMPLETED => response()->json([
                'status'     => 'success',
                'trx_id'     => $transaction->trx_id,
                'amount'     => $transaction->amount,
                'fee'        => $transaction->fee,
                'currency'   => $transaction->currency,
                'net_amount' => $transaction->net_amount,
                'customer'   => [
                    'name'  => $transaction->trx_data['customer_name']  ?? null,
                    'email' => $transaction->trx_data['customer_email'] ?? null,
                ],
                'description' => $transaction->description,
                'created_at'  => $transaction->created_at,
                'updated_at'  => $transaction->updated_at,
            ]),
            TrxStatus::FAILED, TrxStatus::CANCELED => response()->json([
                'status'  => 'failed',
                'trx_id'  => $transaction->trx_id,
                'message' => 'Payment failed or canceled.',
            ]),
            default => response()->json([
                'status'  => 'pending',
                'trx_id'  => $transaction->trx_id,
                'message' => 'Payment is still pending.',
            ]),
        };
    }

    public function siteInfo()
    {
        return response()->json([
            'site_name'           => setting('site_title'),
            'site_logo'           => asset(setting('logo')),
            'site_url'            => url('/'),
            'gateway_name'        => setting('site_title').' Payment Gateway',
            'gateway_description' => 'Secure payment powered by '.setting('site_title'),
            'security_message'    => 'Your payment information is processed securely. We do not store credit card details.',
            'features'            => [
                'ssl_secured'        => '🔒 SSL Secured',
                'instant_processing' => '⚡ Instant',
                'global_support'     => '🌍 Global',
                'mobile_ready'       => '📱 Mobile Ready',
            ],
            'branding' => [
                'primary_color'   => '#28a745',
                'secondary_color' => '#20c997',
                'powered_by_text' => 'Powered by '.setting('site_title'),
            ],
            'environments' => [
                'production' => 'Production Mode - Live payment processing is active',
                'sandbox'    => 'Test Mode - This is a test transaction. No real money will be charged',
            ],
            'api_version' => '1.0',
            'status'      => 'active',
        ]);
    }

    /**
     * Normalize allow_payment_methods input to an array of lowercase keywords.
     * Accepts: null | string (CSV/pipe/space separated) | array of strings.
     */
    private function normalizeAllowPaymentMethods(null|string|array $input): array
    {
        if ($input === null) {
            return [];
        }

        $items = [];
        if (is_string($input)) {
            // Split by comma, pipe, or whitespace
            $items = preg_split('/[\s,|]+/', $input, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } elseif (is_array($input)) {
            $items = $input;
        }

        // Normalize: trim, lowercase, remove empties & duplicates, limit length
        $items = array_map(static function ($v) {
            $v = trim((string) $v);
            $v = Str::lower($v);

            return Str::limit($v, 60, '');
        }, $items);

        return array_values(array_unique(array_filter($items, static fn ($v) => $v !== '')));
    }

    /**
     * @return array{restricted: bool, ids: array<int, int>, codes: array<int, string>}
     */
    private function merchantPaymentMethodSelection(Merchant $merchant, string $currencyCode): array
    {
        $merchant->loadMissing('paymentMethods.paymentGateway');

        $restricted = $merchant->paymentMethods->isNotEmpty();
        $methods    = app(MerchantService::class)->configuredPaymentMethodsForCurrency($merchant, $currencyCode)
            ->filter(fn (DepositMethod $method): bool => $method->type === MethodType::AUTOMATIC && (bool) $method->status)
            ->values();

        return [
            'restricted' => $restricted,
            'ids'        => $methods->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
            'codes'      => $methods->pluck('method_code')->filter()->map(fn ($code): string => (string) $code)->values()->all(),
        ];
    }
}
