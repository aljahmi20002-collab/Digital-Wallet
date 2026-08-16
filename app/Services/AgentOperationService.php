<?php

namespace App\Services;

use App\Data\TransactionData;
use App\Enums\AgentOperationType;
use App\Enums\AgentStatus;
use App\Enums\AmountFlow;
use App\Enums\MethodType;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Models\Agent;
use App\Models\AgentOperation;
use App\Models\NotificationTemplate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\TemplateNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentOperationService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly TransactionService $transactions,
        private readonly AgentCommissionRuleService $commissionRules,
    ) {}

    /**
     * @param array<string, mixed> $validated
     */
    public function cashIn(User $agentUser, array $validated): AgentOperation
    {
        return $this->perform($agentUser, $validated, AgentOperationType::CASH_IN);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function cashOut(User $agentUser, array $validated): AgentOperation
    {
        return $this->perform($agentUser, $validated, AgentOperationType::CASH_OUT);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function sendCashOutOtp(User $agentUser, array $validated): void
    {
        $amount = round((float) $validated['amount'], $this->decimalPlaces());

        [$agent, $agentWallet, $customerWallet] = $this->cashOutOtpParties($agentUser, $validated);

        if ((float) $customerWallet->balance < $amount) {
            throw new NotifyErrorException(__('Insufficient balance in :wallet.', ['wallet' => $customerWallet->currency->code.' '.__('wallet')]));
        }

        $code    = $this->generateCashOutOtp();
        $minutes = $this->cashOutOtpExpiresMinutes();

        Cache::put(
            $this->cashOutOtpCacheKey($agent, $agentWallet, $customerWallet, $amount),
            [
                'hash'     => Hash::make($code),
                'attempts' => 0,
                'sent_at'  => now()->toISOString(),
            ],
            now()->addMinutes($minutes)
        );

        $this->notifyWithTemplate(
            $customerWallet->user,
            'agent_assisted_cash_out_otp',
            [
                'agent_name'      => $agent->agent_name,
                'customer'        => $customerWallet->user->name,
                'amount'          => $customerWallet->currency?->code.' '.number_format($amount, $this->decimalPlaces()),
                'otp'             => $code,
                'expires_minutes' => $minutes,
            ],
            $agentUser,
            route('user.agent.index', ['tab' => 'counter-cashout'])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $user): array
    {
        $agentIds = Agent::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        $today = now()->startOfDay();
        $month = now()->startOfMonth();

        $todayCompletedOperationQuery = fn () => AgentOperation::query()
            ->whereIn('agent_id', $agentIds)
            ->where('status', TrxStatus::COMPLETED)
            ->where('created_at', '>=', $today);

        $operations = AgentOperation::query()
            ->whereIn('agent_id', $agentIds)
            ->where('status', TrxStatus::COMPLETED)
            ->with(['customer', 'currency'])
            ->latest()
            ->limit(8)
            ->get();

        $pendingQrCashOuts = AgentOperation::query()
            ->whereIn('agent_id', $agentIds)
            ->where('type', AgentOperationType::CASH_OUT)
            ->where('status', TrxStatus::PENDING)
            ->where('metadata->channel', 'static_agent_qr')
            ->with(['customer', 'currency'])
            ->latest()
            ->limit(8)
            ->get();

        $commissions = Transaction::query()
            ->where('user_id', $user->id)
            ->where('trx_type', TrxType::AGENT_COMMISSION)
            ->where('status', TrxStatus::COMPLETED)
            ->latest()
            ->limit(8)
            ->get();

        return [
            'approvedAgents' => Agent::query()
                ->where('user_id', $user->id)
                ->where('status', AgentStatus::APPROVED)
                ->with(['currency', 'supportedCurrencies'])
                ->latest()
                ->get(),
            'wallets' => $user->wallets()
                ->with('currency')
                ->where('status', true)
                ->latest()
                ->get(),
            'recentOperations'  => $operations,
            'recentCommissions' => $commissions,
            'pendingQrCashOuts' => $pendingQrCashOuts,
            'stats'             => [
                'today_volume'  => (float) $todayCompletedOperationQuery()->sum('amount'),
                'today_cash_in' => (float) $todayCompletedOperationQuery()
                    ->where('type', AgentOperationType::CASH_IN)
                    ->sum('amount'),
                'today_cash_out' => (float) $todayCompletedOperationQuery()
                    ->where('type', AgentOperationType::CASH_OUT)
                    ->sum('amount'),
                'today_customers' => (int) $todayCompletedOperationQuery()
                    ->distinct('customer_user_id')
                    ->count('customer_user_id'),
                'today_operations' => (int) $todayCompletedOperationQuery()->count(),
                'today_commission' => (float) Transaction::query()
                    ->where('user_id', $user->id)
                    ->where('trx_type', TrxType::AGENT_COMMISSION)
                    ->where('status', TrxStatus::COMPLETED)
                    ->where('created_at', '>=', $today)
                    ->sum('amount'),
                'month_commission' => (float) Transaction::query()
                    ->where('user_id', $user->id)
                    ->where('trx_type', TrxType::AGENT_COMMISSION)
                    ->where('status', TrxStatus::COMPLETED)
                    ->where('created_at', '>=', $month)
                    ->sum('amount'),
                'completed_operations' => AgentOperation::query()
                    ->whereIn('agent_id', $agentIds)
                    ->where('status', TrxStatus::COMPLETED)
                    ->count(),
                'pending_qr_cash_out' => AgentOperation::query()
                    ->whereIn('agent_id', $agentIds)
                    ->where('type', AgentOperationType::CASH_OUT)
                    ->where('status', TrxStatus::PENDING)
                    ->where('metadata->channel', 'static_agent_qr')
                    ->count(),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function customerQrCashOut(User $customer, Agent $agent, array $validated): AgentOperation
    {
        $amount = round((float) $validated['amount'], $this->decimalPlaces());
        $note   = trim((string) ($validated['note'] ?? '')) ?: null;

        return DB::transaction(function () use ($customer, $agent, $validated, $amount, $note): AgentOperation {
            $agent = Agent::query()
                ->whereKey($agent->id)
                ->with(['user', 'supportedCurrencies'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $agent->isApproved() || ! $agent->qr_enabled) {
                throw new NotifyErrorException(__('This agent QR is not available for cash-out.'));
            }

            if ((int) $agent->user_id === (int) $customer->id) {
                throw new NotifyErrorException(__('You cannot cash-out from your own agent QR.'));
            }

            $customerWallet = Wallet::query()
                ->where('user_id', $customer->id)
                ->where('status', true)
                ->whereKey($validated['wallet_id'])
                ->with(['currency', 'user'])
                ->lockForUpdate()
                ->first();

            if (! $customerWallet) {
                throw new NotifyErrorException(__('Select an active wallet to cash-out from this agent.'));
            }

            $operationCurrencyId = (int) $customerWallet->currency_id;

            if (! $agent->supportsCurrency($operationCurrencyId)) {
                throw new NotifyErrorException(__('This agent does not support your selected wallet currency.'));
            }

            $agentWallet = Wallet::query()
                ->where('user_id', $agent->user_id)
                ->where('currency_id', $operationCurrencyId)
                ->where('status', true)
                ->with(['currency', 'user'])
                ->lockForUpdate()
                ->first();

            if (! $agentWallet) {
                throw new NotifyErrorException(__('Agent wallet is not active for this currency.'));
            }

            $this->ensureCustomerPinIsValid($customer, (string) ($validated['wallet_pin'] ?? ''));

            $commission       = $this->commissionRules->calculate($agent, AgentOperationType::CASH_OUT, $amount, $operationCurrencyId);
            $commissionAmount = $commission->amount;

            $this->debit($customerWallet, $amount);
            $this->credit($agentWallet, $amount);

            if ($commissionAmount > 0) {
                $this->credit($agentWallet, $commissionAmount);
            }

            $reference          = $this->reference();
            $walletTransactions = $this->createWalletTransactions(
                agent: $agent,
                agentWallet: $agentWallet->refresh(),
                customerWallet: $customerWallet->refresh(),
                type: AgentOperationType::CASH_OUT,
                amount: $amount,
                commissionAmount: $commissionAmount,
                commissionSnapshot: $commission->snapshot,
                reference: $reference,
                note: $note
            );

            $operation = AgentOperation::query()->create([
                'reference'                 => $reference,
                'agent_id'                  => $agent->id,
                'customer_user_id'          => $customerWallet->user_id,
                'agent_wallet_id'           => $agentWallet->id,
                'customer_wallet_id'        => $customerWallet->id,
                'currency_id'               => $operationCurrencyId,
                'commission_rule_id'        => $commission->ruleId,
                'agent_transaction_id'      => $walletTransactions['agent']->id,
                'customer_transaction_id'   => $walletTransactions['customer']->id,
                'commission_transaction_id' => $walletTransactions['commission']?->id,
                'type'                      => AgentOperationType::CASH_OUT,
                'amount'                    => $amount,
                'commission_amount'         => $commissionAmount,
                'status'                    => TrxStatus::PENDING,
                'note'                      => $note,
                'metadata'                  => [
                    'channel'             => 'static_agent_qr',
                    'cash_handover'       => 'awaiting_agent_confirmation',
                    'commission'          => $commission->snapshot,
                    'commission_source'   => $commission->source,
                    'customer_identifier' => $customerWallet->uuid,
                ],
            ]);

            $operation->load(['agent.user', 'customer', 'currency']);
            $this->notifyQrCashOutCreated($operation);

            return $operation;
        }, 3);
    }

    public function markQrCashOutPaid(User $agentUser, AgentOperation $operation): AgentOperation
    {
        return DB::transaction(function () use ($agentUser, $operation): AgentOperation {
            $operation = AgentOperation::query()
                ->whereKey($operation->id)
                ->with(['agent.user', 'customer', 'currency'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $operation->agent->user_id !== (int) $agentUser->id) {
                throw new NotifyErrorException(__('This cash-out request does not belong to your agent account.'));
            }

            if ($operation->type !== AgentOperationType::CASH_OUT || ($operation->metadata['channel'] ?? null) !== 'static_agent_qr') {
                throw new NotifyErrorException(__('Only static QR cash-out requests can be marked as cash paid here.'));
            }

            if ($operation->status !== TrxStatus::PENDING) {
                throw new NotifyErrorException(__('This cash-out request is already resolved.'));
            }

            $metadata                  = $operation->metadata ?? [];
            $metadata['cash_handover'] = 'paid_by_agent';
            $metadata['cash_paid_by']  = $agentUser->id;
            $metadata['cash_paid_at']  = now()->toISOString();

            $operation->update([
                'status'       => TrxStatus::COMPLETED,
                'metadata'     => $metadata,
                'processed_at' => now(),
            ]);

            $operation->refresh()->load(['agent.user', 'customer', 'currency']);
            $this->notifyQrCashOutCompleted($operation);

            return $operation;
        }, 3);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function perform(User $agentUser, array $validated, AgentOperationType $type): AgentOperation
    {
        $amount = round((float) $validated['amount'], $this->decimalPlaces());
        $note   = trim((string) ($validated['note'] ?? '')) ?: null;

        return DB::transaction(function () use ($agentUser, $validated, $type, $amount, $note): AgentOperation {
            $agent = Agent::query()
                ->where('user_id', $agentUser->id)
                ->whereKey($validated['agent_id'])
                ->with('supportedCurrencies')
                ->lockForUpdate()
                ->firstOrFail();

            if (! $agent->isApproved()) {
                throw new NotifyErrorException(__('Only approved agents can process cash operations.'));
            }

            $agentWallet = Wallet::query()
                ->where('user_id', $agentUser->id)
                ->whereIn('currency_id', $agent->supportedCurrencyIds())
                ->where('status', true)
                ->whereKey($validated['wallet_id'])
                ->with('currency')
                ->lockForUpdate()
                ->first();

            if (! $agentWallet) {
                throw new NotifyErrorException(__('Select an active wallet for one of this agent account currencies.'));
            }

            $operationCurrencyId = (int) $agentWallet->currency_id;

            if (! $agent->supportsCurrency($operationCurrencyId)) {
                throw new NotifyErrorException(__('This agent account does not support the selected wallet currency.'));
            }

            $customerWallet = $this->wallets->getWalletByUserEmailOrWalletUid((string) $validated['customer'], $operationCurrencyId);

            if (! $customerWallet || ! $customerWallet->status || (int) $customerWallet->currency_id !== $operationCurrencyId) {
                throw new NotifyErrorException(__('Customer wallet not found or inactive for this currency.'));
            }

            if ((int) $customerWallet->user_id === (int) $agentUser->id) {
                throw new NotifyErrorException(__('Agent operations cannot be processed against your own wallet.'));
            }

            $customerWallet = Wallet::query()
                ->whereKey($customerWallet->id)
                ->with(['currency', 'user'])
                ->lockForUpdate()
                ->firstOrFail();

            $commission       = $this->commissionRules->calculate($agent, $type, $amount, $operationCurrencyId);
            $commissionAmount = $commission->amount;

            if ($type === AgentOperationType::CASH_IN) {
                $this->debit($agentWallet, $amount);
                $this->credit($customerWallet, $amount);
            }

            if ($type === AgentOperationType::CASH_OUT) {
                $this->ensureCashOutOtpIsValid($agent, $agentWallet, $customerWallet, $amount, (string) ($validated['customer_otp'] ?? ''));
                $this->debit($customerWallet, $amount);
                $this->credit($agentWallet, $amount);
            }

            if ($commissionAmount > 0) {
                $this->credit($agentWallet, $commissionAmount);
            }

            $reference          = $this->reference();
            $walletTransactions = $this->createWalletTransactions(
                agent: $agent,
                agentWallet: $agentWallet->refresh(),
                customerWallet: $customerWallet->refresh(),
                type: $type,
                amount: $amount,
                commissionAmount: $commissionAmount,
                commissionSnapshot: $commission->snapshot,
                reference: $reference,
                note: $note
            );

            $operation = AgentOperation::query()->create([
                'reference'                 => $reference,
                'agent_id'                  => $agent->id,
                'customer_user_id'          => $customerWallet->user_id,
                'agent_wallet_id'           => $agentWallet->id,
                'customer_wallet_id'        => $customerWallet->id,
                'currency_id'               => $operationCurrencyId,
                'commission_rule_id'        => $commission->ruleId,
                'agent_transaction_id'      => $walletTransactions['agent']->id,
                'customer_transaction_id'   => $walletTransactions['customer']->id,
                'commission_transaction_id' => $walletTransactions['commission']?->id,
                'type'                      => $type,
                'amount'                    => $amount,
                'commission_amount'         => $commissionAmount,
                'status'                    => TrxStatus::COMPLETED,
                'note'                      => $note,
                'metadata'                  => [
                    'commission'          => $commission->snapshot,
                    'commission_source'   => $commission->source,
                    'customer_identifier' => (string) $validated['customer'],
                ],
                'processed_at' => now(),
            ]);

            return $operation->load(['customer', 'currency', 'commissionRule']);
        }, 3);
    }

    /**
     * @param  array<string, mixed>                                                           $commissionSnapshot
     * @return array{agent: Transaction, customer: Transaction, commission: Transaction|null}
     */
    private function createWalletTransactions(
        Agent $agent,
        Wallet $agentWallet,
        Wallet $customerWallet,
        AgentOperationType $type,
        float $amount,
        float $commissionAmount,
        array $commissionSnapshot,
        string $reference,
        ?string $note,
    ): array {
        $agentFlow    = $type === AgentOperationType::CASH_IN ? AmountFlow::MINUS : AmountFlow::PLUS;
        $customerFlow = $type === AgentOperationType::CASH_IN ? AmountFlow::PLUS : AmountFlow::MINUS;
        $trxType      = $type === AgentOperationType::CASH_IN ? TrxType::AGENT_CASH_IN : TrxType::AGENT_CASH_OUT;

        $agentTransaction = $this->transactions->create(new TransactionData(
            user_id: (int) $agentWallet->user_id,
            trx_type: $trxType,
            amount: $amount,
            amount_flow: $agentFlow,
            fee: 0,
            currency: $agentWallet->currency->code,
            provider: 'agent',
            processing_type: MethodType::AUTOMATIC,
            net_amount: $amount,
            payable_amount: $amount,
            payable_currency: $agentWallet->currency->code,
            wallet_reference: $agentWallet->uuid,
            trx_data: [
                'agent_id'        => $agent->id,
                'agent_reference' => $reference,
                'customer_wallet' => $customerWallet->uuid,
                'commission'      => $commissionSnapshot,
            ],
            remarks: $note,
            description: $this->agentDescription($type, $customerWallet->user->name),
            status: TrxStatus::COMPLETED
        ));

        $customerTransaction = $this->transactions->create(new TransactionData(
            user_id: (int) $customerWallet->user_id,
            trx_type: $trxType,
            amount: $amount,
            amount_flow: $customerFlow,
            fee: 0,
            currency: $customerWallet->currency->code,
            provider: 'agent',
            processing_type: MethodType::AUTOMATIC,
            net_amount: $amount,
            payable_amount: $amount,
            payable_currency: $customerWallet->currency->code,
            wallet_reference: $customerWallet->uuid,
            trx_reference: $agentTransaction->trx_id,
            trx_data: [
                'agent_id'        => $agent->id,
                'agent_reference' => $reference,
                'agent_wallet'    => $agentWallet->uuid,
                'commission'      => $commissionSnapshot,
            ],
            remarks: $note,
            description: $this->customerDescription($type, $agent->agent_name),
            status: TrxStatus::COMPLETED
        ));

        $agentTransaction->update(['trx_reference' => $customerTransaction->trx_id]);

        $commissionTransaction = null;
        if ($commissionAmount > 0) {
            $commissionTransaction = $this->transactions->create(new TransactionData(
                user_id: (int) $agentWallet->user_id,
                trx_type: TrxType::AGENT_COMMISSION,
                amount: $commissionAmount,
                amount_flow: AmountFlow::PLUS,
                fee: 0,
                currency: $agentWallet->currency->code,
                provider: 'agent',
                processing_type: MethodType::AUTOMATIC,
                net_amount: $commissionAmount,
                payable_amount: $commissionAmount,
                payable_currency: $agentWallet->currency->code,
                wallet_reference: $agentWallet->uuid,
                trx_reference: $agentTransaction->trx_id,
                trx_data: [
                    'agent_id'        => $agent->id,
                    'agent_reference' => $reference,
                    'operation_type'  => $type->value,
                    'commission'      => $commissionSnapshot,
                ],
                description: __('Commission earned from :operation', ['operation' => $type->label()]),
                status: TrxStatus::COMPLETED
            ));
        }

        return [
            'agent'      => $agentTransaction,
            'customer'   => $customerTransaction,
            'commission' => $commissionTransaction,
        ];
    }

    private function debit(Wallet $wallet, float $amount): void
    {
        if ($amount <= 0) {
            throw new NotifyErrorException(__('Amount must be greater than zero.'));
        }

        if ((float) $wallet->balance < $amount) {
            throw new NotifyErrorException(__('Insufficient balance in :wallet.', ['wallet' => $wallet->currency->code.' '.__('wallet')]));
        }

        $wallet->decrement('balance', $amount);
        $wallet->refresh();
    }

    private function credit(Wallet $wallet, float $amount): void
    {
        if ($amount <= 0) {
            throw new NotifyErrorException(__('Amount must be greater than zero.'));
        }

        $wallet->increment('balance', $amount);
        $wallet->refresh();
    }

    /**
     * @param  array<string, mixed>                  $validated
     * @return array{0: Agent, 1: Wallet, 2: Wallet}
     */
    private function cashOutOtpParties(User $agentUser, array $validated): array
    {
        $agent = Agent::query()
            ->where('user_id', $agentUser->id)
            ->whereKey($validated['agent_id'])
            ->with('supportedCurrencies')
            ->firstOrFail();

        if (! $agent->isApproved()) {
            throw new NotifyErrorException(__('Only approved agents can process cash operations.'));
        }

        $agentWallet = Wallet::query()
            ->where('user_id', $agentUser->id)
            ->whereIn('currency_id', $agent->supportedCurrencyIds())
            ->where('status', true)
            ->whereKey($validated['wallet_id'])
            ->with(['currency', 'user'])
            ->first();

        if (! $agentWallet) {
            throw new NotifyErrorException(__('Select an active wallet for one of this agent account currencies.'));
        }

        $operationCurrencyId = (int) $agentWallet->currency_id;

        if (! $agent->supportsCurrency($operationCurrencyId)) {
            throw new NotifyErrorException(__('This agent account does not support the selected wallet currency.'));
        }

        $customerWallet = $this->wallets->getWalletByUserEmailOrWalletUid((string) $validated['customer'], $operationCurrencyId);

        if (! $customerWallet || ! $customerWallet->status || (int) $customerWallet->currency_id !== $operationCurrencyId) {
            throw new NotifyErrorException(__('Customer wallet not found or inactive for this currency.'));
        }

        if ((int) $customerWallet->user_id === (int) $agentUser->id) {
            throw new NotifyErrorException(__('Agent operations cannot be processed against your own wallet.'));
        }

        $customerWallet = Wallet::query()
            ->whereKey($customerWallet->id)
            ->with(['currency', 'user'])
            ->firstOrFail();

        return [$agent, $agentWallet, $customerWallet];
    }

    private function ensureCashOutOtpIsValid(Agent $agent, Wallet $agentWallet, Wallet $customerWallet, float $amount, string $otp): void
    {
        $key     = $this->cashOutOtpCacheKey($agent, $agentWallet, $customerWallet, $amount);
        $payload = Cache::get($key);

        if (! is_array($payload) || ! isset($payload['hash'])) {
            throw new NotifyErrorException(__('Send a customer OTP before completing assisted cash-out.'));
        }

        $maxAttempts = (int) config('mobile_services.phone_verification.max_attempts', 5);
        $attempts    = (int) ($payload['attempts'] ?? 0);

        if ($attempts >= $maxAttempts) {
            Cache::forget($key);

            throw new NotifyErrorException(__('Too many wrong OTP attempts. Send a new customer OTP.'));
        }

        if (! Hash::check($otp, (string) $payload['hash'])) {
            $payload['attempts'] = $attempts + 1;
            Cache::put($key, $payload, now()->addMinutes($this->cashOutOtpExpiresMinutes()));

            throw new NotifyErrorException(__('Customer OTP is incorrect.'));
        }

        Cache::forget($key);
    }

    private function cashOutOtpCacheKey(Agent $agent, Wallet $agentWallet, Wallet $customerWallet, float $amount): string
    {
        return implode(':', [
            'agent-cash-out-otp',
            $agent->id,
            $agentWallet->id,
            $customerWallet->id,
            $this->normalizedOtpAmount($amount),
        ]);
    }

    private function normalizedOtpAmount(float $amount): string
    {
        return number_format($amount, $this->decimalPlaces(), '.', '');
    }

    private function cashOutOtpExpiresMinutes(): int
    {
        return max(1, (int) config('mobile_services.phone_verification.expires_minutes', 10));
    }

    private function generateCashOutOtp(): string
    {
        $testingCode = (string) config('mobile_services.phone_verification.testing_code', '');

        if (app()->environment('testing') && $testingCode !== '') {
            return $testingCode;
        }

        $length = max(4, (int) config('mobile_services.phone_verification.code_length', 6));
        $max    = (10 ** $length) - 1;
        $number = random_int(0, $max);

        return str_pad((string) $number, $length, '0', STR_PAD_LEFT);
    }

    private function ensureCustomerPinIsValid(User $customer, string $pin): void
    {
        if (! $customer->hasWalletPin()) {
            throw new NotifyErrorException(__('Customer must set a Wallet PIN before cash-out.'));
        }

        if (! Hash::check($pin, $customer->wallet_pin)) {
            throw new NotifyErrorException(__('Customer Wallet PIN is incorrect.'));
        }
    }

    private function reference(): string
    {
        do {
            $reference = 'AGX'.Str::upper(Str::random(12));
        } while (AgentOperation::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function decimalPlaces(): int
    {
        return max(2, min(8, (int) setting('site_decimal', 2)));
    }

    private function agentDescription(AgentOperationType $type, string $customerName): string
    {
        return match ($type) {
            AgentOperationType::CASH_IN  => __('Agent cash-in processed for :customer', ['customer' => $customerName]),
            AgentOperationType::CASH_OUT => __('Agent cash-out received from :customer', ['customer' => $customerName]),
        };
    }

    private function customerDescription(AgentOperationType $type, string $agentName): string
    {
        return match ($type) {
            AgentOperationType::CASH_IN  => __('Cash-in received from :agent', ['agent' => $agentName]),
            AgentOperationType::CASH_OUT => __('Cash-out processed by :agent', ['agent' => $agentName]),
        };
    }

    private function notifyQrCashOutCreated(AgentOperation $operation): void
    {
        $this->notifyWithTemplate(
            $operation->agent->user,
            'agent_qr_cash_out_requested',
            [
                'agent_name'    => $operation->agent->agent_name,
                'customer'      => $operation->customer?->name,
                'amount'        => $this->money($operation),
                'reference'     => $operation->reference,
                'currency'      => $operation->currency?->code,
                'cash_out_link' => route('user.agent.index', ['tab' => 'cash-out-requests']),
            ],
            $operation->customer,
            route('user.agent.index', ['tab' => 'cash-out-requests'])
        );

        $this->notifyWithTemplate(
            $operation->customer,
            'agent_qr_cash_out_customer_confirmed',
            [
                'agent_name' => $operation->agent->agent_name,
                'amount'     => $this->money($operation),
                'reference'  => $operation->reference,
            ],
            $operation->agent->user,
            route('user.transaction.index')
        );
    }

    private function notifyQrCashOutCompleted(AgentOperation $operation): void
    {
        $this->notifyWithTemplate(
            $operation->customer,
            'agent_qr_cash_out_cash_paid',
            [
                'agent_name' => $operation->agent->agent_name,
                'amount'     => $this->money($operation),
                'reference'  => $operation->reference,
            ],
            $operation->agent->user,
            route('user.transaction.index')
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function notifyWithTemplate(mixed $notifiable, string $identifier, array $data, mixed $sender = null, ?string $action = null): void
    {
        if (! $notifiable || ! NotificationTemplate::query()->where('identifier', $identifier)->exists()) {
            return;
        }

        $notifiable->notify(new TemplateNotification(
            identifier: $identifier,
            data: $data,
            sender: $sender,
            action: $action
        ));
    }

    private function money(AgentOperation $operation): string
    {
        return (string) $operation->currency?->code.' '.number_format($operation->amount, $this->decimalPlaces());
    }
}
