<?php

namespace App\Services;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\MethodType;
use App\Enums\MobileRechargeStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Models\MobileRecharge;
use App\Models\MobileRechargeProvider;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\MobileRecharge\MobileRechargeProviderManager;
use App\Services\MobileRecharge\MobileRechargeProviderResult;
use App\Services\MobileRecharge\MobileRechargeQuote;
use Illuminate\Support\Facades\DB;
use Throwable;

class MobileRechargeService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly TransactionService $transactions,
        private readonly MobileRechargeProviderManager $providers,
    ) {}

    /**
     * @throws NotifyErrorException
     */
    public function quote(float $amount, string $currency): MobileRechargeQuote
    {
        $provider = $this->providers->resolveActiveProvider();

        $this->ensureAmountWithinLimits($amount, $provider);

        $fee = $provider
            ? $provider->calculateFee($amount)
            : $this->calculateLegacyFee($amount);

        return new MobileRechargeQuote(
            amount: round($amount, 8),
            fee: $fee,
            total: round($amount + $fee, 8),
            currency: $currency,
        );
    }

    private function calculateLegacyFee(float $amount): float
    {
        $fixed   = (float) setting('mobile_recharge_fee_fixed', config('mobile_services.recharge.fee_fixed', 0));
        $percent = (float) setting('mobile_recharge_fee_percent', config('mobile_services.recharge.fee_percent', 0));

        return round($fixed + ($amount * $percent / 100), 8);
    }

    /**
     * @throws NotifyErrorException
     */
    public function recharge(User $user, Wallet $wallet, string $phoneNumber, float $amount, ?string $operator = null, ?string $country = null): MobileRecharge
    {
        $this->ensureRechargeAllowed($user, $wallet);

        $provider = $this->providers->resolveActiveProvider();

        $this->ensureProviderSupports($provider, $wallet->currency->code, $country);

        $quote = $this->quote($amount, $wallet->currency->code);

        if (! $this->wallets->isWalletBalanceSufficient($wallet->uuid, $quote->total)) {
            throw new NotifyErrorException(__('Insufficient balance.'));
        }

        $providerCode = $provider?->code ?? $this->providers->providerCode();

        [$recharge, $transaction] = DB::transaction(function () use ($user, $wallet, $phoneNumber, $operator, $country, $quote, $providerCode): array {
            $this->wallets->subtractMoney($wallet, $quote->total);

            $recharge = MobileRecharge::query()->create([
                'user_id'      => $user->id,
                'wallet_id'    => $wallet->id,
                'phone_number' => $this->normalizePhoneNumber($phoneNumber),
                'operator'     => $operator,
                'country'      => $country ? strtoupper($country) : null,
                'amount'       => $quote->amount,
                'fee'          => $quote->fee,
                'total_amount' => $quote->total,
                'currency'     => $quote->currency,
                'provider'     => $providerCode,
                'status'       => MobileRechargeStatus::PENDING,
                'metadata'     => [],
            ]);

            $transaction = $this->transactions->create(new TransactionData(
                user_id: $user->id,
                trx_type: TrxType::MOBILE_RECHARGE,
                amount: $quote->amount,
                amount_flow: AmountFlow::MINUS,
                fee: $quote->fee,
                currency: $quote->currency,
                provider: $providerCode,
                processing_type: MethodType::AUTOMATIC,
                net_amount: $quote->amount,
                payable_amount: $quote->total,
                payable_currency: $quote->currency,
                wallet_reference: $wallet->uuid,
                trx_data: [
                    'mobile_recharge_id' => $recharge->id,
                    'phone_number'       => $recharge->phone_number,
                    'operator'           => $recharge->operator,
                    'country'            => $recharge->country,
                ],
                description: __('Mobile recharge for :phone', ['phone' => $recharge->phone_number]),
                status: TrxStatus::PENDING,
            ));

            $recharge->update(['transaction_id' => $transaction->id]);

            return [$recharge->refresh(), $transaction->refresh()];
        }, 3);

        try {
            $result = $this->providers->activeProvider()->recharge($recharge->load('transaction'));

            return $this->applyProviderResult($recharge, $transaction, $result);
        } catch (Throwable $e) {
            report($e);

            return $this->failAndRefund($recharge, $transaction, $e->getMessage());
        }
    }

    /**
     * @throws NotifyErrorException
     */
    private function ensureRechargeAllowed(User $user, Wallet $wallet): void
    {
        if ((int) $wallet->user_id !== (int) $user->id) {
            throw new NotifyErrorException(__('The selected wallet is invalid.'));
        }

        if (! $wallet->status) {
            throw new NotifyErrorException(__('The selected wallet is not active.'));
        }

    }

    /**
     * @throws NotifyErrorException
     */
    private function ensureAmountWithinLimits(float $amount, ?MobileRechargeProvider $provider = null): void
    {
        if ($provider) {
            $min = (float) ($provider->min_amount ?? 0);
            $max = $provider->max_amount !== null ? (float) $provider->max_amount : 0;
        } else {
            $min = (float) setting('mobile_recharge_min_amount', config('mobile_services.recharge.min_amount', 10));
            $max = (float) setting('mobile_recharge_max_amount', config('mobile_services.recharge.max_amount', 10000));
        }

        if ($min > 0 && $amount < $min) {
            throw new NotifyErrorException(__('Amount must be at least :min.', ['min' => $min]));
        }

        if ($max > 0 && $amount > $max) {
            throw new NotifyErrorException(__('Amount must not exceed :max.', ['max' => $max]));
        }
    }

    /**
     * @throws NotifyErrorException
     */
    private function ensureProviderSupports(?MobileRechargeProvider $provider, string $currency, ?string $country): void
    {
        if (! $provider) {
            return;
        }

        if (! $provider->supportsCurrency($currency)) {
            throw new NotifyErrorException(__('The selected provider does not support the wallet currency :currency.', [
                'currency' => $currency,
            ]));
        }

        if ($country && ! $provider->supportsCountry($country)) {
            throw new NotifyErrorException(__('The selected provider does not support recharge in :country.', [
                'country' => strtoupper($country),
            ]));
        }
    }

    private function applyProviderResult(
        MobileRecharge $recharge,
        Transaction $transaction,
        MobileRechargeProviderResult $result
    ): MobileRecharge {
        if ($result->status === MobileRechargeStatus::FAILED) {
            return $this->failAndRefund($recharge, $transaction, $result->message ?? __('Mobile recharge failed.'));
        }

        $transactionStatus = $result->status === MobileRechargeStatus::COMPLETED
            ? TrxStatus::COMPLETED
            : TrxStatus::PENDING;

        DB::transaction(function () use ($recharge, $transaction, $result, $transactionStatus): void {
            $recharge->update([
                'status'             => $result->status,
                'provider_reference' => $result->reference,
                'failure_reason'     => null,
                'metadata'           => $result->payload,
                'processed_at'       => $result->status === MobileRechargeStatus::COMPLETED ? now() : null,
            ]);

            $transaction->update([
                'status'        => $transactionStatus,
                'trx_reference' => $result->reference,
                'remarks'       => $result->message,
            ]);
        }, 3);

        return $recharge->refresh();
    }

    private function failAndRefund(MobileRecharge $recharge, Transaction $transaction, string $reason): MobileRecharge
    {
        DB::transaction(function () use ($recharge, $transaction, $reason): void {
            if ($transaction->status !== TrxStatus::FAILED) {
                $this->wallets->addMoneyByWalletUuid($transaction->wallet_reference, (float) $transaction->payable_amount);

                $this->transactions->create(new TransactionData(
                    user_id: (int) $transaction->user_id,
                    trx_type: TrxType::REFUND,
                    amount: (float) $transaction->payable_amount,
                    amount_flow: AmountFlow::PLUS,
                    fee: 0,
                    currency: $transaction->payable_currency ?? $transaction->currency,
                    provider: 'system',
                    processing_type: MethodType::AUTOMATIC,
                    net_amount: (float) $transaction->payable_amount,
                    payable_amount: (float) $transaction->payable_amount,
                    payable_currency: $transaction->payable_currency ?? $transaction->currency,
                    wallet_reference: $transaction->wallet_reference,
                    trx_reference: $transaction->trx_id,
                    trx_data: [
                        'refunded_trx_id' => $transaction->trx_id,
                        'source'          => 'mobile_recharge',
                    ],
                    remarks: $reason,
                    description: __('Refund for failed mobile recharge'),
                    status: TrxStatus::COMPLETED,
                ));
            }

            $recharge->update([
                'status'         => MobileRechargeStatus::FAILED,
                'failure_reason' => $reason,
                'processed_at'   => now(),
            ]);

            $transaction->update([
                'status'  => TrxStatus::FAILED,
                'remarks' => $reason,
            ]);
        }, 3);

        return $recharge->refresh();
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/[^\d+]/', '', $phoneNumber) ?? $phoneNumber;
    }
}
