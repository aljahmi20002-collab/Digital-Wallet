<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AmountFlow;
use App\Enums\PaymentLinkStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\UserStatus;
use App\Exceptions\NotifyErrorException;
use App\Models\Currency;
use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Handlers\PaymentHandler;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;
use Wallet;

class PaymentLinkService
{
    /**
     * Paginated list of payment links for a given owner.
     */
    public function listForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return PaymentLink::query()
            ->ownedBy($userId)
            ->with(['currency', 'merchant.supportedCurrencies'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Admin-side paginated listing across all users with filters. Stays in
     * the service so the controller can remain thin and the same query
     * shape can be reused (e.g. by exports/reports later).
     *
     * Supported $filters keys: status, search, merchant_id, currency_id,
     * date_from, date_to, has_payments.
     *
     * @param array<string, mixed> $filters
     */
    public function adminListing(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->adminQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * High-level metrics for the admin dashboard header. Kept in a single
     * helper so the controller doesn't run multiple ad-hoc count queries.
     *
     * @return array{total: int, active: int, inactive: int, payments: int}
     */
    public function adminMetrics(): array
    {
        $base = PaymentLink::query();

        return [
            'total'    => (clone $base)->count(),
            'active'   => (clone $base)->where('status', PaymentLinkStatus::ACTIVE->value)->count(),
            'inactive' => (clone $base)->where('status', PaymentLinkStatus::INACTIVE->value)->count(),
            'payments' => (int) (clone $base)->sum('payments_count'),
        ];
    }

    /**
     * Admin-only status flip. Returns the refreshed model.
     */
    public function adminToggleStatus(PaymentLink $link): PaymentLink
    {
        return $this->toggleStatus($link);
    }

    /**
     * Admin-only soft delete.
     */
    public function adminDelete(PaymentLink $link): void
    {
        $link->delete();
    }

    /**
     * Build the admin listing query without executing it. Splitting this
     * out keeps `adminListing` thin and makes the same filters reusable
     * for counts / exports.
     *
     * @param array<string, mixed> $filters
     */
    protected function adminQuery(array $filters): Builder
    {
        $query = PaymentLink::query()
            ->with(['user', 'merchant.supportedCurrencies', 'currency'])
            ->latest();

        if (! empty($filters['status']) && in_array($filters['status'], PaymentLinkStatus::all(), true)) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['merchant_id'])) {
            $query->where('merchant_id', (int) $filters['merchant_id']);
        }

        if (! empty($filters['currency_id'])) {
            $query->where('currency_id', (int) $filters['currency_id']);
        }

        if (! empty($filters['has_payments'])) {
            $query->where('payments_count', '>', 0);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('token', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search): void {
                        $u->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Create a new payment link for the given user.
     *
     * If `merchant_id` is supplied, currency and wallet are derived from
     * the selected merchant shop. The merchant must be owned by the user
     * and not in a locked state.
     *
     * @param array<string, mixed> $data
     *
     * @throws NotifyErrorException
     */
    public function create(User $user, array $data): PaymentLink
    {
        $this->normaliseAmounts($data);

        $merchant = $this->resolveMerchant($user, $data['merchant_id'] ?? null);

        if ($merchant !== null) {
            $currency    = $this->resolveMerchantCurrency($merchant, $data['currency_id'] ?? null);
            $merchantFee = (float) $merchant->fee;
        } else {
            $currency    = Currency::query()->findOrFail($data['currency_id']);
            $merchantFee = null;
        }

        $wallet = Wallet::getWalletByUserId($user->id, $currency->code);
        if (! $wallet) {
            throw new NotifyErrorException(__('Receiver wallet for :currency is not available.', ['currency' => $currency->code]));
        }

        return PaymentLink::query()->create([
            'user_id'          => $user->id,
            'merchant_id'      => $merchant?->id,
            'currency_id'      => $currency->id,
            'wallet_reference' => $wallet?->uuid,
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'amount'           => $data['amount']      ?? null,
            'min_amount'       => $data['min_amount']  ?? null,
            'max_amount'       => $data['max_amount']  ?? null,
            'merchant_fee'     => $merchantFee,
            'expires_at'       => $data['expires_at']   ?? null,
            'max_payments'     => $data['max_payments'] ?? null,
            'status'           => PaymentLinkStatus::ACTIVE,
        ]);
    }

    /**
     * Update an owned payment link.
     *
     * @param array<string, mixed> $data
     *
     * @throws NotifyErrorException
     */
    public function update(PaymentLink $link, array $data): PaymentLink
    {
        $this->normaliseAmounts($data);

        // Resolve (possibly changed) merchant link first so currency / wallet
        // can be derived from it. The owner of the link is unchanged.
        $owner    = $link->user;
        $merchant = $this->resolveMerchant($owner, $data['merchant_id'] ?? null);

        if ($merchant !== null) {
            $currency = $this->resolveMerchantCurrency($merchant, $data['currency_id'] ?? $link->currency_id);
            $wallet   = Wallet::getWalletByUserId($owner->id, $currency->code);
            if (! $wallet) {
                throw new NotifyErrorException(__('Receiver wallet for :currency is not available.', ['currency' => $currency->code]));
            }

            $link->merchant_id      = $merchant->id;
            $link->merchant_fee     = (float) $merchant->fee;
            $link->currency_id      = $currency->id;
            $link->wallet_reference = $wallet->uuid;
        } else {
            // No merchant linkage — honour the supplied currency_id (when
            // changed) and clear any previous merchant snapshot.
            $link->merchant_id  = null;
            $link->merchant_fee = null;

            if (array_key_exists('currency_id', $data) && (int) $data['currency_id'] !== (int) $link->currency_id) {
                $currency = Currency::query()->findOrFail($data['currency_id']);
                $wallet   = Wallet::getWalletByUserId($owner->id, $currency->code);
                if (! $wallet) {
                    throw new NotifyErrorException(__('Receiver wallet for :currency is not available.', ['currency' => $currency->code]));
                }

                $link->currency_id      = $currency->id;
                $link->wallet_reference = $wallet->uuid;
            }
        }

        $link->fill([
            'title'        => $data['title']        ?? $link->title,
            'description'  => $data['description']  ?? null,
            'amount'       => $data['amount']       ?? null,
            'min_amount'   => $data['min_amount']   ?? null,
            'max_amount'   => $data['max_amount']   ?? null,
            'expires_at'   => $data['expires_at']   ?? null,
            'max_payments' => $data['max_payments'] ?? null,
        ]);

        $link->save();

        return $link;
    }

    /**
     * Toggle status between active and inactive.
     */
    public function toggleStatus(PaymentLink $link): PaymentLink
    {
        $link->status = $link->status === PaymentLinkStatus::ACTIVE
            ? PaymentLinkStatus::INACTIVE
            : PaymentLinkStatus::ACTIVE;

        $link->save();

        return $link;
    }

    /**
     * Resolve a public link by token, asserting it can be paid right now.
     *
     * @throws NotifyErrorException
     */
    public function resolvePayableByToken(string $token): PaymentLink
    {
        $link = PaymentLink::query()
            ->byToken($token)
            ->with(['user', 'currency', 'merchant.supportedCurrencies'])
            ->first();

        if (! $link) {
            throw new NotifyErrorException(__('Payment link not found.'), 404);
        }

        if (! $link->isActive()) {
            throw new NotifyErrorException(__('This payment link is currently inactive.'), 403);
        }

        if ($link->isExpired()) {
            throw new NotifyErrorException(__('This payment link has expired.'), 403);
        }

        if ($link->isMaxedOut()) {
            throw new NotifyErrorException(__('This payment link can no longer accept payments.'), 403);
        }

        if ($link->hasMerchantShop() && $link->merchant?->isActionLocked()) {
            throw new NotifyErrorException(__('This merchant shop is currently unavailable.'), 403);
        }

        return $link;
    }

    /**
     * Create a pending RECEIVE_PAYMENT transaction tied to a payment link
     * for the receiver. Used by the gateway flow so existing IPN/callback
     * code paths can complete the transaction once payment is captured.
     *
     * @throws NotifyErrorException
     */
    public function createPendingReceiverTransaction(PaymentLink $link, float $amount, array $customer = []): Transaction
    {
        if (($error = $link->validatePayAmount($amount)) !== null) {
            throw new NotifyErrorException($error);
        }

        if ($link->hasMerchantShop() && $link->merchant?->isActionLocked()) {
            throw new NotifyErrorException(__('This merchant shop is currently unavailable.'));
        }

        $currencyCode   = $link->currencyCode();
        $receiverWallet = Wallet::getWalletByUserId($link->user_id, $currencyCode);

        if (! $receiverWallet) {
            throw new NotifyErrorException(__('Receiver wallet for this currency is not available.'));
        }

        return Transaction::create($this->preparePendingArray($link, $amount, $receiverWallet->uuid, $customer));
    }

    /**
     * Process a wallet payment from an authenticated payer paying the link.
     *
     * Flow:
     *   - Validate amount, balance, self-payment and (when merchant-linked)
     *     merchant availability.
     *   - Apply merchant fee snapshot (if any) — payer pays full amount,
     *     receiver gets `amount - fee`.
     *   - Debit payer wallet, create payer PAYMENT (-) transaction.
     *   - Credit receiver wallet, create receiver RECEIVE_PAYMENT (+).
     *   - Increment link payments_count.
     *
     * @throws NotifyErrorException
     */
    public function payWithWallet(PaymentLink $link, User $payer, float $amount, array $customer = []): Transaction
    {
        if ($payer->id === $link->user_id) {
            throw new NotifyErrorException(__('You cannot pay your own payment link.'));
        }

        if (($error = $link->validatePayAmount($amount)) !== null) {
            throw new NotifyErrorException($error);
        }

        if ($link->hasMerchantShop() && $link->merchant?->isActionLocked()) {
            throw new NotifyErrorException(__('This merchant shop is currently unavailable.'));
        }

        $currencyCode   = $link->currencyCode();
        $payerWallet    = Wallet::getWalletByUserId($payer->id, $currencyCode);
        $receiverWallet = Wallet::getWalletByUserId($link->user_id, $currencyCode);

        if (! $payerWallet) {
            throw new NotifyErrorException(__('You do not have a :currency wallet to pay this link.', ['currency' => $currencyCode]));
        }

        if (! $receiverWallet) {
            throw new NotifyErrorException(__('Receiver does not have a wallet for this currency.'));
        }

        if (! Wallet::isWalletBalanceSufficient($payerWallet->uuid, $amount)) {
            throw new NotifyErrorException(__('Insufficient wallet balance.'));
        }

        [$fee, $netAmount] = $this->calculateFee($link, $amount);

        return DB::transaction(function () use ($link, $payer, $amount, $fee, $netAmount, $payerWallet, $receiverWallet, $currencyCode, $customer) {
            $handler = app(PaymentHandler::class);

            // Payer side: PAYMENT (MINUS), debits wallet via PaymentHandler.
            $payerTrx = Transaction::create(
                $this->prepareCompletedPayerArray($link, $payer, $payerWallet->uuid, $amount, $fee, $netAmount, $currencyCode, $customer)
            );
            $handler->handleSuccess($payerTrx);

            // Receiver side: RECEIVE_PAYMENT (PLUS), credits wallet via PaymentHandler.
            $receiverTrx = Transaction::create(
                $this->prepareCompletedReceiverArray($link, $receiverWallet->uuid, $amount, $fee, $netAmount, $currencyCode, $payerTrx, $customer)
            );
            $handler->handleSuccess($receiverTrx);

            $link->recordSuccessfulPayment();

            Log::info('Payment link wallet payment completed', [
                'payment_link_id' => $link->id,
                'merchant_id'     => $link->merchant_id,
                'payer_id'        => $payer->id,
                'receiver_id'     => $link->user_id,
                'amount'          => $amount,
                'fee'             => $fee,
                'net_amount'      => $netAmount,
                'currency'        => $currencyCode,
            ]);

            return $receiverTrx;
        });
    }

    /**
     * Process wallet payment for an authenticated payer after Wallet PIN
     * confirmation.
     *
     * @throws NotifyErrorException
     */
    public function payWithWalletUsingPin(PaymentLink $link, User $payer, string $pin, float $amount, array $customer = []): Transaction
    {
        if (! $payer->hasWalletPin()) {
            throw new NotifyErrorException(__('Please set a Wallet PIN in settings before authorising payments.'));
        }

        if (! Hash::check($pin, $payer->wallet_pin)) {
            throw new NotifyErrorException(__('Incorrect Wallet PIN.'));
        }

        if ($payer->status !== UserStatus::ACTIVE) {
            throw new NotifyErrorException(__('This wallet account is not active.'));
        }

        if ($payer->email_verified_at === null) {
            throw new NotifyErrorException(__('This wallet account is not verified.'));
        }

        return $this->payWithWallet($link, $payer, $amount, $customer);
    }

    /**
     * Process wallet payment via wallet uuid + Wallet PIN (mirrors the
     * existing MerchantPaymentReceiveController::completePayment pattern,
     * but for payment links — public flow without a logged-in payer).
     *
     * Hardened to refuse credentials from suspended or unverified wallet
     * users. The same identity gates the authenticated middleware stack
     * (`account.status.check`, `verified`) enforces are applied here too,
     * so a guest who happens to know a PIN cannot bypass them.
     *
     * Bruteforce protection (per-IP + wallet RateLimiter) is layered in by
     * the controller — this service stays transport-agnostic.
     *
     * @throws NotifyErrorException
     */
    public function payWithWalletPinCredentials(PaymentLink $link, string $walletId, string $pin, float $amount, array $customer = []): Transaction
    {
        try {
            $payerWallet = Wallet::getWalletByUniqueId($walletId);
        } catch (NotifyErrorException $e) {
            // Re-throw with a generic message so the wallet uuid space
            // cannot be enumerated through this endpoint.
            throw new NotifyErrorException(__('Wallet ID or PIN is incorrect.'));
        } catch (Throwable $e) {
            throw new NotifyErrorException(__('Wallet ID or PIN is incorrect.'));
        }

        if (! $payerWallet) {
            throw new NotifyErrorException(__('Wallet ID or PIN is incorrect.'));
        }

        $payer = $payerWallet->user;

        if (! $payer) {
            throw new NotifyErrorException(__('Wallet ID or PIN is incorrect.'));
        }

        if (! $payer->hasWalletPin() || ! Hash::check($pin, $payer->wallet_pin)) {
            throw new NotifyErrorException(__('Wallet ID or PIN is incorrect.'));
        }

        if ($payer->status !== UserStatus::ACTIVE) {
            throw new NotifyErrorException(__('This wallet account is not active.'));
        }

        if ($payer->email_verified_at === null) {
            throw new NotifyErrorException(__('This wallet account is not verified.'));
        }

        if ($payerWallet->currency?->code !== $link->currencyCode()) {
            throw new NotifyErrorException(__('Wallet currency does not match the payment link currency.'));
        }

        return $this->payWithWallet($link, $payer, $amount, $customer);
    }

    /**
     * Mark a pending payment link receiver transaction as completed and
     * increment the link's payment counter. Used by gateway IPN/callbacks.
     */
    public function completePendingReceiverTransaction(Transaction $transaction): void
    {
        if ($transaction->status === TrxStatus::COMPLETED) {
            return;
        }

        $linkId = $transaction->trx_data['payment_link_id'] ?? null;
        if ($linkId === null) {
            return;
        }

        DB::transaction(function () use ($transaction) {
            \Transaction::completeTransaction(
                $transaction->trx_id,
                null,
                __('Payment link payment completed.')
            );
        });
    }

    /**
     * Build the JSON `trx_data` payload that identifies a payment-link
     * transaction in downstream handlers, IPN dispatch and history views.
     *
     * @param  array<string, mixed> $customer
     * @return array<string, mixed>
     */
    public function buildTrxData(PaymentLink $link, float $amount, array $customer = []): array
    {
        $receiver = $link->user;
        $merchant = $link->merchant;
        $role     = $receiver?->role?->value ?? 'user';

        return [
            'source'            => 'payment_link',
            'payment_link_id'   => $link->id,
            'token'             => $link->token,
            'receiver_user_id'  => $link->user_id,
            'receiver_role'     => $role,
            'receiver_name'     => $receiver?->name,
            'merchant_id'       => $merchant?->id,
            'merchant_name'     => $merchant?->business_name,
            'merchant_shop_url' => $merchant?->site_url,
            'title'             => $link->title,
            'description'       => $link->description,
            'amount'            => $amount,
            'currency'          => $link->currencyCode(),
            'customer_name'     => $customer['name']  ?? null,
            'customer_email'    => $customer['email'] ?? null,
        ];
    }

    /**
     * Resolve and validate a merchant for the given owner. Returns the
     * approved Merchant model, or null when no merchant is requested.
     *
     * @throws NotifyErrorException
     */
    protected function resolveMerchant(User $owner, mixed $merchantId): ?Merchant
    {
        if ($merchantId === null || $merchantId === '' || $merchantId === '0') {
            return null;
        }

        $merchant = Merchant::query()
            ->with(['currency', 'supportedCurrencies'])
            ->find($merchantId);

        if (! $merchant || (int) $merchant->user_id !== (int) $owner->id) {
            throw new NotifyErrorException(__('Selected merchant shop is not available.'));
        }

        if (! $merchant->isApproved()) {
            throw new NotifyErrorException(__('Selected merchant shop must be approved before it can be used.'));
        }

        if ($merchant->isActionLocked()) {
            throw new NotifyErrorException(__('Selected merchant shop is currently disabled or rejected.'));
        }

        return $merchant;
    }

    /**
     * Resolve the currency a merchant-branded payment link should collect.
     *
     * @throws NotifyErrorException
     */
    protected function resolveMerchantCurrency(Merchant $merchant, mixed $currencyId): Currency
    {
        if ($currencyId === null || $currencyId === '' || $currencyId === '0') {
            $currency = $merchant->primaryCurrency();

            if ($currency instanceof Currency) {
                return $currency;
            }

            throw new NotifyErrorException(__('Selected merchant shop does not have a payment currency configured.'));
        }

        $currency = Currency::query()->find((int) $currencyId);

        if (! $currency || ! $merchant->supportsCurrency((int) $currency->id)) {
            throw new NotifyErrorException(__('Selected currency is not available for this merchant shop.'));
        }

        return $currency;
    }

    /**
     * Calculate fee + net amount for a payment. Merchant-linked payments
     * use the snapshotted percentage; general links carry zero fee.
     *
     * @return array{0: float, 1: float} [fee, netAmount]
     */
    protected function calculateFee(PaymentLink $link, float $amount): array
    {
        $feePct = (float) ($link->merchant_fee ?? 0);

        if ($feePct <= 0) {
            return [0.0, $amount];
        }

        $fee = round($amount * $feePct / 100, 2);
        $net = round($amount - $fee, 2);

        return [$fee, $net];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function normaliseAmounts(array &$data): void
    {
        // Empty strings -> null so we don't fail decimal casts.
        foreach (['amount', 'min_amount', 'max_amount', 'max_payments', 'expires_at'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function preparePendingArray(PaymentLink $link, float $amount, string $receiverWalletUuid, array $customer): array
    {
        $currencyCode      = $link->currencyCode();
        [$fee, $netAmount] = $this->calculateFee($link, $amount);

        return [
            'user_id'          => $link->user_id,
            'trx_type'         => TrxType::RECEIVE_PAYMENT->value,
            'description'      => __('Pending payment link payment: :title', ['title' => $link->title]),
            'provider'         => 'system',
            'processing_type'  => 'auto',
            'amount'           => $netAmount,
            'amount_flow'      => AmountFlow::PLUS->value,
            'fee'              => $fee,
            'currency'         => $currencyCode,
            'net_amount'       => $netAmount,
            'payable_amount'   => $amount,
            'payable_currency' => $currencyCode,
            'wallet_reference' => $receiverWalletUuid,
            'trx_data'         => $this->buildTrxData($link, $amount, $customer),
            'status'           => TrxStatus::PENDING->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareCompletedReceiverArray(PaymentLink $link, string $receiverWalletUuid, float $amount, float $fee, float $netAmount, string $currencyCode, Transaction $payerTrx, array $customer): array
    {
        return [
            'user_id'          => $link->user_id,
            'trx_type'         => TrxType::RECEIVE_PAYMENT->value,
            'description'      => __('Payment received via payment link: :title', ['title' => $link->title]),
            'provider'         => 'system',
            'processing_type'  => 'auto',
            'amount'           => $netAmount,
            'amount_flow'      => AmountFlow::PLUS->value,
            'fee'              => $fee,
            'currency'         => $currencyCode,
            'net_amount'       => $netAmount,
            'payable_amount'   => $amount,
            'payable_currency' => $currencyCode,
            'wallet_reference' => $receiverWalletUuid,
            'trx_data'         => $this->buildTrxData($link, $amount, $customer),
            'trx_reference'    => $payerTrx->trx_id,
            'status'           => TrxStatus::COMPLETED->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareCompletedPayerArray(PaymentLink $link, User $payer, string $payerWalletUuid, float $amount, float $fee, float $netAmount, string $currencyCode, array $customer): array
    {
        return [
            'user_id'          => $payer->id,
            'trx_type'         => TrxType::PAYMENT->value,
            'description'      => __('Payment via payment link: :title', ['title' => $link->title]),
            'provider'         => 'system',
            'processing_type'  => 'auto',
            'amount'           => $amount,
            'amount_flow'      => AmountFlow::MINUS->value,
            'fee'              => $fee,
            'currency'         => $currencyCode,
            'net_amount'       => $amount,
            'payable_amount'   => $amount,
            'payable_currency' => $currencyCode,
            'wallet_reference' => $payerWalletUuid,
            'trx_data'         => $this->buildTrxData($link, $amount, $customer),
            'status'           => TrxStatus::COMPLETED->value,
        ];
    }
}
