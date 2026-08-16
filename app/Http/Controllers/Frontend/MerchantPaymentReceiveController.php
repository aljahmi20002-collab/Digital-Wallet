<?php

namespace App\Http\Controllers\Frontend;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\EnvironmentMode;
use App\Enums\MethodType;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Models\DepositMethod;
use App\Models\Merchant;
use App\Models\Transaction as TransactionModel;
use App\Models\Voucher;
use App\Services\Handlers\PaymentHandler;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Payment;
use Throwable;
use Transaction;
use Wallet;

class MerchantPaymentReceiveController extends Controller
{
    /**
     * Display the payment checkout page.
     *
     * @return Factory|Application|\Illuminate\View\View|object|View
     */
    public function paymentCheckoutSigned(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, __('Invalid or expired link'));
        }

        $token = $request->query('token');

        if (! $token) {
            abort(403, __('Invalid transaction request'));
        }

        try {
            $trxId = Crypt::decryptString($token);
        } catch (Exception $e) {
            Log::error($e);
            abort(403, __('Invalid or expired token'));
        }

        $transaction = Transaction::findTransaction($trxId);

        if (! $transaction || $transaction->status !== TrxStatus::PENDING) {
            abort(404, 'Not Valid Transaction');
        }

        $merchant = Merchant::findOrFail($transaction->trx_data['merchant_id'])
            ->only(['business_name', 'business_logo']);

        // Detect environment mode from transaction data
        $environment = $this->detectEnvironmentMode($transaction);
        $isSandbox   = $environment->isSandbox();

        $data = array_merge($transaction->trx_data, $merchant, [
            'payment_amount'          => "{$transaction->payable_amount} {$transaction->payable_currency}",
            'environment'             => $environment->value,
            'is_sandbox'              => $isSandbox,
            'environment_label'       => $environment->getLabel(),
            'environment_badge_class' => $environment->getBadgeClass(),
        ]);

        $paymentMethods = $this->availableGatewayMethodsForTransaction($transaction);

        // Add sandbox-specific transaction marking
        if ($isSandbox) {
            $data['sandbox_notice']         = __('This is a sandbox transaction. No real money will be processed.');
            $data['sandbox_transaction_id'] = $transaction->trx_id;

            // Log sandbox payment attempt
            Log::info('Sandbox payment checkout accessed', [
                'transaction_id' => $transaction->trx_id,
                'merchant_id'    => $transaction->trx_data['merchant_id'],
                'amount'         => $transaction->payable_amount,
                'currency'       => $transaction->payable_currency,
                'environment'    => $environment->value,
            ]);
        }

        return view('general.merchant.payment_checkout', compact('data', 'paymentMethods', 'trxId'));
    }

    public function paymentCheckoutPublic(string $merchant, string $token)
    {
        // 1. Retrieve the transaction using the custom scope
        $transaction = TransactionModel::byTrxToken($token)->firstOrFail();

        // 2. Ensure the transaction is still pending
        if ($transaction->status !== TrxStatus::PENDING) {
            abort(404, __('Invalid or already processed transaction.'));
        }

        // 3. Check if the transaction has expired using model method
        if ($transaction->isExpired()) {
            abort(403, __('This payment link has expired.'));
        }

        // 4. Retrieve the merchant from the transaction data
        $merchantModel = Merchant::find($transaction->trx_data['merchant_id']);

        // 5. Match the URL-provided merchant slug with actual merchant's business name
        if (! $merchantModel || Str::slug($merchantModel->business_name) !== $merchant) {
            abort(403, __('Merchant mismatch or not found.'));
        }

        // Detect environment mode from transaction data
        $environment = $this->detectEnvironmentMode($transaction);
        $isSandbox   = $environment->isSandbox();

        // 6. Prepare data to pass to the payment view
        $data = array_merge($transaction->trx_data, [
            'business_name'           => $merchantModel->business_name,
            'business_logo'           => $merchantModel->business_logo,
            'payment_amount'          => "{$transaction->payable_amount} {$transaction->payable_currency}",
            'expires_at'              => $transaction->formattedExpiresAt(), // Optional: formatted for UI display
            'environment'             => $environment->value,
            'is_sandbox'              => $isSandbox,
            'environment_label'       => $environment->getLabel(),
            'environment_badge_class' => $environment->getBadgeClass(),
        ]);

        // Add sandbox-specific handling
        if ($isSandbox) {
            $data['sandbox_notice']         = __('This is a sandbox transaction. No real money will be processed.');
            $data['sandbox_transaction_id'] = $transaction->trx_id;

            // Log sandbox payment attempt
            Log::info('Sandbox payment checkout (public) accessed', [
                'transaction_id' => $transaction->trx_id,
                'merchant_id'    => $transaction->trx_data['merchant_id'],
                'merchant_slug'  => $merchant,
                'amount'         => $transaction->payable_amount,
                'currency'       => $transaction->payable_currency,
                'environment'    => $environment->value,
            ]);
        }

        // 7. Load available automatic deposit methods based on transaction currency
        $paymentMethods = $this->availableGatewayMethodsForTransaction($transaction);

        $trxId = $transaction->trx_id;

        // 8. Return the checkout view with the necessary data
        return view('general.merchant.payment_checkout', compact('data', 'paymentMethods', 'trxId'));
    }

    /**
     * Process the selected payment method.
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'trx_id'          => 'required',
            'selected_method' => 'required',
        ]);

        $transaction = Transaction::findTransaction($validated['trx_id']);

        if (! $transaction || $transaction->status !== TrxStatus::PENDING) {
            abort(404, __('Invalid or already processed transaction.'));
        }

        // Detect environment mode
        $environment = $this->detectEnvironmentMode($transaction);
        $isSandbox   = $environment->isSandbox();

        // Log payment processing start
        Log::info('Payment processing started', [
            'transaction_id' => $validated['trx_id'],
            'payment_method' => $validated['selected_method'],
            'environment'    => $environment->value,
            'is_sandbox'     => $isSandbox,
        ]);

        if ($validated['selected_method'] === MethodType::SYSTEM->value) {
            $token = Crypt::encryptString($validated['trx_id']); // Encrypt trxId for extra security

            $signedUrl = URL::temporarySignedRoute(
                'payment.wallet.pay',
                now()->addMinutes(10),
                ['token' => $token]
            );

            return redirect()->away($signedUrl);
        }

        $paymentMethod = $this->availableGatewayMethodsForTransaction($transaction)
            ->firstWhere('method_code', $validated['selected_method']);

        if (! $paymentMethod) {
            throw new NotifyErrorException(__('Selected payment gateway is not available for this merchant currency.'));
        }

        // Sandbox auto-completion for gateway payments
        if ($isSandbox) {
            // Auto-complete payment for sandbox mode
            $merchantName = $transaction->trx_data['merchant_name'];
            $description  = __('SANDBOX: Payment via :provider from :merchant (Auto-completed)', [
                'provider' => $paymentMethod->name,
                'merchant' => $merchantName,
            ]);

            // Complete the transaction immediately
            Transaction::completeTransaction($validated['trx_id'], null, $description);

            // Log sandbox auto-completion
            Log::info('Sandbox gateway payment auto-completed', [
                'transaction_id' => $validated['trx_id'],
                'payment_method' => $validated['selected_method'],
                'environment'    => $environment->value,
            ]);

            notifyEvs('success', __('Sandbox payment completed successfully!'));

            // Redirect to success page
            $successUrl = $transaction->trx_data['success_redirect'] ?? route('status.success');

            return redirect()->away($successUrl);
        }

        $provider = $paymentMethod->name;

        // Update transaction with environment-aware description
        $merchantName = $transaction->trx_data['merchant_name'];
        $description  = __('Payment via :provider from :merchant', ['provider' => $provider, 'merchant' => $merchantName]);

        $transaction->update([
            'provider'    => $provider,
            'description' => $description,
        ]);

        // Get the payment gateway URL from the service
        $redirectUrl = Payment::paymentWithPaymentMethod(
            $paymentMethod->method_code,
            $transaction
        );

        // Log payment gateway redirect
        Log::info('Payment gateway redirect', [
            'transaction_id' => $validated['trx_id'],
            'payment_method' => $validated['selected_method'],
            'provider'       => $provider,
            'environment'    => $environment->value,
            'redirect_url'   => is_string($redirectUrl) ? substr($redirectUrl, 0, 100) : 'HTML_CONTENT',
        ]);

        // Redirect the user to the payment gateway
        if (filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            // Redirect the user to the payment gateway for URL
            return redirect()->away($redirectUrl);
        } else {
            // Return the HTML view directly if $redirectUrl is HTML content
            return response($redirectUrl, 200)->header('Content-Type', 'text/html');
        }
    }

    /**
     * Complete payment using wallet credentials provided by the user.
     *
     * @throws NotifyErrorException
     */
    public function completePayment(Request $request): RedirectResponse
    {
        $voucherResult = $this->voucherPayment($request);
        if ($voucherResult instanceof RedirectResponse) {
            return $voucherResult;
        }
        // Default: Wallet payment with PIN authentication
        $validated = $request->validate([
            'trx_id'    => 'required',
            'pin'       => 'required|digits:6',
            'wallet_id' => 'required',
        ]);

        $merchantTransaction = Transaction::findTransaction($validated['trx_id']);
        if (! $merchantTransaction) {
            throw new NotifyErrorException(__('Transaction not found.'));
        }

        if ($merchantTransaction->status !== TrxStatus::PENDING) {
            throw new NotifyErrorException(__('Invalid or already processed transaction.'));
        }

        $trxData  = $merchantTransaction->trx_data;
        $merchant = Merchant::findOrFail($trxData['merchant_id']);

        // Detect environment mode
        $environment = $this->detectEnvironmentMode($merchantTransaction);
        $isSandbox   = $environment->isSandbox();

        // Handle sandbox test credentials
        if ($isSandbox && $validated['wallet_id'] === '123456789' && $validated['pin'] === '123456') {
            $description = __('SANDBOX: Payment via Demo Wallet from :merchant customer (Auto-completed)',
                ['merchant' => $merchant->business_name]);

            Transaction::completeTransaction($merchantTransaction->trx_id, null, $description);

            Log::info('Sandbox demo wallet payment completed', [
                'transaction_id' => $merchantTransaction->trx_id,
                'wallet_id'      => $validated['wallet_id'],
                'environment'    => $environment->value,
            ]);

            notifyEvs('success', __('Demo wallet payment completed successfully!'));

            $redirectUrl = $trxData['success_redirect'] ?? route('status.success');

            return redirect()->away($redirectUrl);
        }

        $userWallet = Wallet::getWalletByUniqueId($validated['wallet_id']);

        if (! $userWallet) {
            throw new NotifyErrorException(__('Wallet not found.'));
        }

        if ($userWallet->currency?->code !== $this->transactionCurrency($merchantTransaction)) {
            throw new NotifyErrorException(__('Wallet currency does not match the payment currency.'));
        }

        $user = $userWallet->user;

        if (! $user->hasWalletPin()) {
            throw new NotifyErrorException(__('This wallet user has not set a Wallet PIN yet. Please log in and configure one before paying.'));
        }

        $this->guardWalletPinAttempts($user, $request, $merchantTransaction);

        if (! Hash::check($validated['pin'], $user->wallet_pin)) {
            $this->recordWalletPinFailure($user, $request, $merchantTransaction);

            throw new NotifyErrorException(__('Incorrect Wallet PIN for this wallet user.'));
        }

        $this->clearWalletPinAttempts($user, $request);

        return $this->processMerchantTransaction($userWallet, $merchantTransaction, $user, $merchant);
    }

    /**
     * Complete payment using the currently authenticated user's wallet.
     *
     * @throws NotifyErrorException
     */
    public function payWithAccount(Request $request): RedirectResponse
    {
        if ($request->isMethod('get')) {
            $token = $request->get('token');

            $payload = Payment::verifyTokenAndGetData($token);

            if (! isset($payload['trx_id'])) {
                throw new NotifyErrorException('Invalid or expired token.');
            }
            $trxId = $payload['trx_id'];
        } else {
            $validated = $request->validate([
                'trx_id' => 'required',
                'pin'    => 'required|digits:6',
            ]);
            $trxId = $validated['trx_id'];
        }

        $merchantTransaction = Transaction::findTransaction($trxId);

        if (! $merchantTransaction || $merchantTransaction->status !== TrxStatus::PENDING) {
            abort(404, __('Invalid or already processed transaction.'));
        }
        $trxData  = $merchantTransaction->trx_data;
        $merchant = Merchant::findOrFail($trxData['merchant_id']);

        $environment = $this->detectEnvironmentMode($merchantTransaction);
        $isSandbox   = $environment->isSandbox();

        // Auto-complete login payment for sandbox mode
        if ($isSandbox) {
            $description = __('SANDBOX: Payment via Login from :merchant customer (Auto-completed)',
                ['merchant' => $merchant->business_name]);

            Transaction::completeTransaction($merchantTransaction->trx_id, null, $description);

            Log::info('Sandbox login payment auto-completed', [
                'transaction_id' => $merchantTransaction->trx_id,
                'environment'    => $environment->value,
            ]);

            notifyEvs('success', __('Login payment completed successfully!'));

            $redirectUrl = $trxData['success_redirect'] ?? route('status.success');

            return redirect()->away($redirectUrl);
        }

        // For GET (after token redirect): redirect to the wallet payment form so the user can enter PIN.
        if ($request->isMethod('get')) {
            $newToken  = Crypt::encryptString($trxId);
            $signedUrl = URL::temporarySignedRoute(
                'payment.wallet.pay',
                now()->addMinutes(10),
                ['token' => $newToken]
            );

            return redirect()->away($signedUrl);
        }

        $user = auth()->user();

        if (! $user->hasWalletPin()) {
            throw new NotifyErrorException(__('Please set a Wallet PIN in settings before authorising payments.'));
        }

        $this->guardWalletPinAttempts($user, $request, $merchantTransaction);

        if (! Hash::check($validated['pin'], $user->wallet_pin)) {
            $this->recordWalletPinFailure($user, $request, $merchantTransaction);

            throw new NotifyErrorException(__('Incorrect Wallet PIN.'));
        }

        $this->clearWalletPinAttempts($user, $request);

        $userWallet = Wallet::getWalletByUserId($user->id, $this->transactionCurrency($merchantTransaction));

        if (! $userWallet) {
            throw new NotifyErrorException(__('You do not have a wallet for this payment currency.'));
        }

        return $this->processMerchantTransaction($userWallet, $merchantTransaction, $user, $merchant);
    }

    public function walletPayment(Request $request, $token)
    {
        // Validate the signed route (expiry, tampering)
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired payment link.');
        }

        // Decrypt trx_id from token
        try {
            $trx_id = Crypt::decryptString($token);
        } catch (Exception $e) {
            abort(403, 'Invalid or expired token.');
        }

        // Find transaction and ensure it's pending (one-time use)
        $transaction = Transaction::findTransaction($trx_id);
        if (! $transaction || $transaction->status !== TrxStatus::PENDING) {
            abort(404, 'Invalid or already processed transaction.');
        }

        // Detect environment mode
        $environment = $this->detectEnvironmentMode($transaction);
        $isSandbox   = $environment->isSandbox();

        // (Optional) Rate limit by IP or add attempt logging here
        $data = array_merge($transaction->trx_data, [
            'payment_amount'          => "{$transaction->payable_amount} {$transaction->payable_currency}",
            'currency'                => $transaction->payable_currency,
            'environment'             => $environment->value,
            'is_sandbox'              => $isSandbox,
            'environment_label'       => $environment->getLabel(),
            'environment_badge_class' => $environment->getBadgeClass(),
        ]);

        // Add sandbox-specific data
        if ($isSandbox) {
            $data['sandbox_notice']         = __('This is a sandbox transaction. No real money will be processed.');
            $data['sandbox_transaction_id'] = $transaction->trx_id;

            // Log sandbox wallet payment access
            Log::info('Sandbox wallet payment accessed', [
                'transaction_id' => $transaction->trx_id,
                'merchant_id'    => $transaction->trx_data['merchant_id'],
                'amount'         => $transaction->payable_amount,
                'currency'       => $transaction->payable_currency,
                'environment'    => $environment->value,
            ]);
        }

        return view('general.merchant.payment_wallet', [
            'data'  => $data,
            'trxId' => $trx_id,
        ]);
    }

    /**
     * Handle a failed transaction by sending an IPN notification and redirecting.
     */
    protected function handleFailure($transaction, string $errorMessage, ?string $description = null): RedirectResponse
    {
        // Detect environment for proper logging
        $environment = $this->detectEnvironmentMode($transaction);
        $isSandbox   = $environment->isSandbox();

        // Log failure with environment context
        Log::error('Payment transaction failed', [
            'transaction_id' => $transaction->trx_id ?? 'unknown',
            'environment'    => $environment->value,
            'is_sandbox'     => $isSandbox,
            'error_message'  => $errorMessage,
            'description'    => $description,
        ]);

        Transaction::failTransaction($transaction->trx_id, $errorMessage, $description);

        return redirect()->away($transaction->trx_data['cancel_redirect'] ?? route('status.cancel'));
    }

    /**
     * Handle voucher payment with sandbox environment support.
     */
    private function voucherPayment(Request $request)
    {
        $isVoucher = $request->has('voucher_code') && ! empty($request->input('voucher_code'));

        if (! $isVoucher) {
            return null;
        }

        $validated = $request->validate([
            'trx_id'       => 'required',
            'voucher_code' => 'required|string|max:32',
        ]);

        $trxId       = $validated['trx_id'];
        $voucherCode = $validated['voucher_code'];

        $merchantTransaction = Transaction::findTransaction($trxId);
        if (! $merchantTransaction || $merchantTransaction->status !== TrxStatus::PENDING) {
            throw new NotifyErrorException(__('Invalid or expired transaction.'));
        }

        $trxData  = $merchantTransaction->trx_data;
        $merchant = Merchant::findOrFail($trxData['merchant_id']);

        // Detect environment mode
        $environment = $this->detectEnvironmentMode($merchantTransaction);
        $isSandbox   = $environment->isSandbox();

        // Handle sandbox test voucher
        if ($isSandbox && $voucherCode === 'TESTVOUCHER') {
            // Auto-complete payment for demo voucher
            $description = __('SANDBOX: Payment via Demo Voucher from :merchant customer (Auto-completed)',
                ['merchant' => $merchant->business_name]);

            Transaction::completeTransaction($merchantTransaction->trx_id, null, $description);

            // Log sandbox demo voucher payment
            Log::info('Sandbox demo voucher payment completed', [
                'transaction_id' => $merchantTransaction->trx_id,
                'voucher_code'   => $voucherCode,
                'environment'    => $environment->value,
            ]);

            notifyEvs('success', __('Demo voucher payment completed successfully!'));

            $redirectUrl = $trxData['success_redirect'] ?? route('status.success');

            return redirect()->to($redirectUrl);
        }

        DB::beginTransaction();
        try {
            $voucher = Voucher::where('code', $voucherCode)->lockForUpdate()->first();
            if (! $voucher || ! $voucher->isValid()) {
                throw new NotifyErrorException(__('Invalid or already redeemed voucher.'));
            }

            if ($voucher->amount < $merchantTransaction->amount || $voucher->currency->code !== $merchantTransaction->currency) {
                throw new NotifyErrorException(__('Voucher does not match payment requirements.'));
            }

            // Mark voucher as redeemed
            $voucher->is_active          = false;
            $voucher->redeemed_by        = $merchantTransaction->user_id   ?? null;
            $voucher->redeemed_wallet_id = $merchantTransaction->wallet_id ?? null;
            $voucher->redeemed_at        = now();
            $voucher->save();

            // Environment-aware completion description
            $completionDescription = $isSandbox
                ? __('SANDBOX: You have received a payment via Voucher from :merchant customer', ['merchant' => $merchant->business_name])
                : __('You have received a payment via Voucher from :merchant customer', ['merchant' => $merchant->business_name]);

            // Complete the transaction (use your transaction handler for consistency)
            Transaction::completeTransaction(
                $merchantTransaction->trx_id,
                null,
                $completionDescription
            );

            DB::commit();

            // Log voucher payment completion
            Log::info('Voucher payment completed', [
                'transaction_id' => $merchantTransaction->trx_id,
                'voucher_code'   => $voucherCode,
                'merchant_id'    => $merchant->id,
                'amount'         => $merchantTransaction->amount,
                'currency'       => $merchantTransaction->currency,
                'environment'    => $environment->value,
                'is_sandbox'     => $isSandbox,
            ]);

            notifyEvs('success', __('Voucher payment completed successfully.'));

            $redirectUrl = $trxData['success_redirect'] ?? route('status.success');

            return redirect()->to($redirectUrl);

        } catch (Throwable $e) {
            DB::rollBack();

            // Log voucher payment failure with environment context
            Log::error('Voucher payment failed', [
                'transaction_id' => $merchantTransaction->trx_id,
                'voucher_code'   => $voucherCode,
                'environment'    => $environment->value,
                'is_sandbox'     => $isSandbox,
                'error'          => $e->getMessage(),
            ]);

            throw new NotifyErrorException($e->getMessage());
        }
    }

    /**
     * Throw a NotifyErrorException when the user has exceeded the wallet PIN attempt cap.
     *
     * @throws NotifyErrorException
     */
    private function guardWalletPinAttempts(mixed $user, Request $request, mixed $merchantTransaction): void
    {
        $key = $this->walletPinThrottleKey($user, $request);

        if (RateLimiter::tooManyAttempts($key, $this->walletPinAttemptLimit())) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('Wallet PIN locked out', [
                'user_id'        => $user->id,
                'ip'             => $request->ip(),
                'transaction_id' => $merchantTransaction->trx_id ?? null,
                'attempt_limit'  => $this->walletPinAttemptLimit(),
                'retry_after'    => $seconds,
            ]);

            throw new NotifyErrorException(__('Too many incorrect Wallet PIN attempts. Try again in :minutes minutes.', [
                'minutes' => max(1, (int) ceil($seconds / 60)),
            ]));
        }
    }

    /**
     * Increment the rate-limiter and log the failure for forensic review.
     */
    private function recordWalletPinFailure(mixed $user, Request $request, mixed $merchantTransaction): void
    {
        RateLimiter::hit($this->walletPinThrottleKey($user, $request), $this->walletPinLockSeconds());

        Log::warning('Wallet PIN check failed', [
            'user_id'        => $user->id,
            'ip'             => $request->ip(),
            'transaction_id' => $merchantTransaction->trx_id ?? null,
        ]);
    }

    /**
     * Clear the rate-limiter on a successful PIN match.
     */
    private function clearWalletPinAttempts(mixed $user, Request $request): void
    {
        RateLimiter::clear($this->walletPinThrottleKey($user, $request));
    }

    /**
     * Build the rate-limit cache key scoped per (user, IP).
     */
    private function walletPinThrottleKey(mixed $user, Request $request): string
    {
        return 'wallet-pin:'.$user->id.':'.$request->ip();
    }

    private function walletPinAttemptLimit(): int
    {
        return max(3, min(10, (int) config('security.wallet_pin_attempt_limit', 5)));
    }

    private function walletPinLockSeconds(): int
    {
        return max(1, min(60, (int) config('security.wallet_pin_lock_minutes', 15))) * 60;
    }

    /**
     * Detect environment mode from transaction data or merchant configuration.
     */
    private function detectEnvironmentMode($transaction): EnvironmentMode
    {
        // Check if environment is stored in transaction data
        if (isset($transaction->trx_data['environment'])) {
            return EnvironmentMode::from($transaction->trx_data['environment']);
        }

        // Check if is_sandbox flag is set in transaction data
        if (isset($transaction->trx_data['is_sandbox'])) {
            return $transaction->trx_data['is_sandbox'] ? EnvironmentMode::SANDBOX : EnvironmentMode::PRODUCTION;
        }

        // Fallback: Check merchant configuration
        $merchant = Merchant::find($transaction->trx_data['merchant_id']);
        if ($merchant && $merchant->sandbox_enabled) {
            // Check if transaction was created with sandbox credentials
            // This can be determined by checking transaction remarks or other indicators
            if (str_contains($transaction->remarks ?? '', 'SANDBOX_TRANSACTION')) {
                return EnvironmentMode::SANDBOX;
            }
        }

        // Default to production if no environment indicators found
        return EnvironmentMode::PRODUCTION;
    }

    /**
     * Process the merchant transaction after validations.
     */
    private function processMerchantTransaction(mixed $userWallet, mixed $merchantTransaction, mixed $user, mixed $merchant): RedirectResponse
    {
        // Detect environment mode
        $environment = $this->detectEnvironmentMode($merchantTransaction);
        $isSandbox   = $environment->isSandbox();

        if ($merchantTransaction->status !== TrxStatus::PENDING) {
            throw new NotifyErrorException(__('Invalid or already processed transaction.'));
        }

        if ($user->id === $merchantTransaction->user_id) {
            return $this->handleFailure($merchantTransaction, __('You cannot pay to yourself.'), __('A payment attempt via User Wallet from :merchant customer has failed', ['merchant' => $merchant->business_name]));
        }
        $trxData        = $merchantTransaction->trx_data;
        $payableAmount  = $merchantTransaction->payable_amount;
        $merchantAmount = $merchantTransaction->amount;
        $currencyCode   = $this->transactionCurrency($merchantTransaction);

        if (! $userWallet || $userWallet->currency?->code !== $currencyCode) {
            throw new NotifyErrorException(__('Wallet currency does not match the payment currency.'));
        }

        if (! Wallet::isWalletBalanceSufficient($userWallet->uuid, $payableAmount)) {
            return $this->handleFailure($merchantTransaction, __('Insufficient balance'));
        }

        $merchantWallet = Wallet::getWalletByUserId($merchantTransaction->user_id, $currencyCode);

        if (! $merchantWallet) {
            return $this->handleFailure($merchantTransaction, __('Merchant wallet for this currency is not available.'));
        }

        try {
            $this->executeTransaction($userWallet, $merchantWallet, $payableAmount, $merchantAmount, $user, $merchant, $environment, $currencyCode);

            // Environment-aware completion description
            $completionDescription = $isSandbox
                ? __('SANDBOX: You have received a payment via User Wallet from :merchant customer', ['merchant' => $merchant->business_name])
                : __('You have received a payment via User Wallet from :merchant customer', ['merchant' => $merchant->business_name]);

            Transaction::completeTransaction($merchantTransaction->trx_id, null, $completionDescription);

            notifyEvs('success', $completionDescription);

            // Log successful payment
            Log::info('Payment completed successfully', [
                'transaction_id' => $merchantTransaction->trx_id,
                'merchant_id'    => $merchant->id,
                'user_id'        => $user->id,
                'amount'         => $payableAmount,
                'currency'       => $currencyCode,
                'environment'    => $environment->value,
                'is_sandbox'     => $isSandbox,
            ]);

            return redirect()->away($trxData['success_redirect'] ?? route('status.success'));
        } catch (Exception $e) {
            Log::error('Transaction process failed', [
                'transaction_id' => $merchantTransaction->trx_id,
                'environment'    => $environment->value,
                'is_sandbox'     => $isSandbox,
                'error'          => $e->getMessage(),
            ]);

            return $this->handleFailure(
                $merchantTransaction,
                __('Transaction failed'),
                __('A payment attempt via User Wallet from :merchant customer has failed', ['merchant' => $merchant->business_name])
            );
        }
    }

    /**
     * Execute the wallet transaction atomically.
     *
     * @throws Throwable
     */
    private function executeTransaction(
        mixed $userWallet,
        mixed $merchantWallet,
        float $payableAmount,
        float $merchantAmount,
        mixed $user,
        mixed $merchant,
        EnvironmentMode $environment,
        string $currencyCode
    ): void {
        DB::transaction(function () use ($userWallet, $payableAmount, $user, $merchant, $environment, $currencyCode) {
            $paymentHandler = app(PaymentHandler::class);

            $isSandbox = $environment->isSandbox();

            // Environment-aware transaction description
            $description = $isSandbox
                ? __('SANDBOX: Payment to :merchant via your wallet', ['merchant' => $merchant->business_name])
                : __('Payment to :merchant via your wallet', ['merchant' => $merchant->business_name]);

            $transaction = Transaction::create(new TransactionData(
                user_id: $user->id,
                trx_type: TrxType::PAYMENT,
                amount: $payableAmount,
                amount_flow: AmountFlow::MINUS,
                currency: $currencyCode,
                net_amount: $payableAmount,
                payable_amount: $payableAmount,
                payable_currency: $currencyCode,
                wallet_reference: $userWallet->uuid,
                description: $description,
                status: TrxStatus::COMPLETED,
                remarks: $isSandbox ? 'SANDBOX_TRANSACTION' : null
            ));

            $paymentHandler->handleSuccess($transaction);
        });
    }

    private function transactionCurrency(mixed $transaction): string
    {
        return (string) ($transaction->payable_currency ?: $transaction->currency);
    }

    /**
     * @return EloquentCollection<int, DepositMethod>
     */
    private function availableGatewayMethodsForTransaction(mixed $transaction): EloquentCollection
    {
        $currencyCode = $this->transactionCurrency($transaction);

        $methodsQuery = DepositMethod::query()
            ->where('type', MethodType::AUTOMATIC)
            ->where('status', true)
            ->where('currency', $currencyCode);

        [$restrictByMerchantMethods, $merchantPaymentMethodIds] = $this->merchantPaymentMethodFilter($transaction, $currencyCode);

        if ($restrictByMerchantMethods) {
            if ($merchantPaymentMethodIds === []) {
                return new EloquentCollection;
            }

            $methodsQuery->whereIn('id', $merchantPaymentMethodIds);
        }

        $allowedKeywords = $this->normalizedAllowedPaymentMethods($transaction->trx_data['allow_payment_methods'] ?? []);

        if ($allowedKeywords !== []) {
            $methodsQuery->where(function ($query) use ($allowedKeywords): void {
                foreach ($allowedKeywords as $keyword) {
                    $query->orWhereRaw('LOWER(name) LIKE ?', ['%'.$keyword.'%']);
                }
            });
        }

        return $methodsQuery->get();
    }

    /**
     * @return array{0: bool, 1: array<int, int>}
     */
    private function merchantPaymentMethodFilter(mixed $transaction, string $currencyCode): array
    {
        $trxData         = $transaction->trx_data ?? [];
        $restricted      = (bool) ($trxData['merchant_payment_methods_restricted'] ?? false);
        $storedMethodIds = collect($trxData['merchant_payment_method_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($restricted || $storedMethodIds !== []) {
            return [$restricted || $storedMethodIds !== [], $storedMethodIds];
        }

        $merchantId = (int) ($trxData['merchant_id'] ?? 0);

        if ($merchantId <= 0) {
            return [false, []];
        }

        $merchant = Merchant::query()
            ->with('paymentMethods')
            ->find($merchantId);

        if (! $merchant || $merchant->paymentMethods->isEmpty()) {
            return [false, []];
        }

        $methodIds = $merchant->paymentMethods
            ->filter(fn (DepositMethod $method): bool => $method->type === MethodType::AUTOMATIC
                && (bool) $method->status
                && strtoupper((string) $method->currency) === strtoupper($currencyCode))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        return [true, $methodIds];
    }

    /**
     * @return array<int, string>
     */
    private function normalizedAllowedPaymentMethods(mixed $allowed): array
    {
        if (is_string($allowed)) {
            $allowed = preg_split('/[\s,|]+/', $allowed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (! is_array($allowed) || $allowed === []) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(static function (mixed $value): string {
            return trim(mb_strtolower((string) $value));
        }, $allowed))));
    }
}
