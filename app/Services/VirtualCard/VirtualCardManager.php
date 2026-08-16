<?php

declare(strict_types=1);

namespace App\Services\VirtualCard;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\MethodType;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\VirtualCard\VirtualCardFeeOperation;
use App\Enums\VirtualCard\VirtualCardRequestStatus;
use App\Exceptions\NotifyErrorException;
use App\Models\VirtualCard;
use App\Models\VirtualCardFeeSetting;
use App\Models\VirtualCardProvider;
use App\Models\VirtualCardRequest;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\Auth;
use Transaction;

class VirtualCardManager
{
    protected VirtualCardProviderFactory $providerFactory;

    public function __construct(VirtualCardProviderFactory $providerFactory)
    {
        $this->providerFactory = $providerFactory;
    }

    /**
     * Issue a virtual card using the selected provider.
     *
     * @throws Exception
     */
    public function issueProviderCard(VirtualCardRequest $request, string $providerCode): VirtualCard
    {
        $providerModel = VirtualCardProvider::query()
            ->where('code', $providerCode)
            ->first();

        if ($providerModel && (int) $request->provider_id !== (int) $providerModel->id) {
            $request->forceFill(['provider_id' => $providerModel->id]);
        }

        $provider = $this->providerFactory->getProvider($providerCode);
        $cardInfo = $provider->issueCard($request);

        // Carry the user-chosen design forward into the issued card so the
        // dashboard can honour it instead of falling back to the rotating
        // theme wheel keyed on card index.
        $cardMeta          = is_array($cardInfo['meta'] ?? null) ? $cardInfo['meta'] : [];
        $cardMeta['theme'] = $request->theme ?: ($cardMeta['theme'] ?? null);

        // Async-issuing providers (Bitnob) can return status=`failed`
        // when the card was allocated an id but never provisioned. We
        // still save the row so the dashboard has a record the user
        // can inspect and retry from. `last4`, `expiry_month`, and
        // `expiry_year` may all be null on failed rows — the schema
        // was relaxed in the matching migration to allow this.
        $providerStatus = $cardInfo['status'] ?? 'pending';
        $isFailedIssue  = $providerStatus === 'failed';
        $providerId     = $providerModel?->id ?: $request->provider_id;

        $card = VirtualCard::create([
            'virtual_card_request_id' => $request->id,
            'wallet_id'               => $request->wallet_id,
            'user_id'                 => $request->user_id,
            'provider_id'             => $providerId,
            'network'                 => $request->network,
            'provider_card_id'        => $cardInfo['id'],
            'last4'                   => $cardInfo['last4'] ?: null,
            'brand'                   => $cardInfo['brand'],
            'expiry_month'            => $cardInfo['expiry_month'] ?: null,
            'expiry_year'             => $cardInfo['expiry_year'] ?: null,
            'status'                  => $providerStatus,
            'meta'                    => $cardMeta,
        ]);

        $request->update([
            'status' => $isFailedIssue
                ? VirtualCardRequestStatus::Failed
                : VirtualCardRequestStatus::Issued,
            'provider_id'        => $providerId,
            'provider_issued_at' => now(),
            'provider_response'  => $cardInfo['raw'] ?? null,
        ]);

        // We don't throw on `failed` here — the controller wraps this
        // call in a DB transaction and catches Throwable. Throwing
        // would roll back the failed-card row we just saved (defeating
        // the whole point of the row). Instead, the caller checks
        // `$card->status === Failed` and chooses the toast accordingly.
        return $card;
    }

    /**
     * Top up a virtual card.
     *
     * @throws Exception
     */
    public function topup(int $cardId, float $amount): string
    {
        $user        = Auth::user();
        $virtualCard = VirtualCard::where('id', $cardId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wallet     = $virtualCard->wallet;
        $feeSetting = $this->getFeeSetting($virtualCard, VirtualCardFeeOperation::Topup->value);
        $this->validateAmountRange($feeSetting, $amount);
        $fee   = $feeSetting ? (float) $feeSetting->calculateFee($amount) : 0.0;
        $total = $amount + $fee;

        // Check wallet balance
        if ($wallet->balance < $total) {
            throw new NotifyErrorException(__('Insufficient wallet balance.'));
        }

        $provider      = $this->providerFactory->getProvider($virtualCard->provider->code);
        $topUpResponse = $provider->topUpCard($amount, $virtualCard->provider_card_id);

        // Persist the new on-card balance to `meta['balance']` so the
        // dashboard's mini-card and hero progress reflect the topup.
        $meta            = is_array($virtualCard->meta) ? $virtualCard->meta : [];
        $currentBalance  = (float) ($meta['balance'] ?? 0);
        $meta['balance'] = round($currentBalance + (float) $amount, 2);
        $virtualCard->update(['meta' => $meta]);

        $details = [
            'trxData'       => $topUpResponse,
            'amount'        => $amount,
            'charge'        => $fee,
            'netAmount'     => $amount,
            'payableAmount' => $total,
        ];

        $this->createTransactionData($details, $virtualCard->provider, $wallet, TrxType::CARD_TOPUP);

        return 'success';
    }

    /**
     * Withdraw from a virtual card.
     *
     * @throws Exception
     */
    public function withdraw(int $cardId, float $amount): string
    {
        $user        = Auth::user();
        $virtualCard = VirtualCard::where('id', $cardId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wallet     = $virtualCard->wallet;
        $feeSetting = $this->getFeeSetting($virtualCard, VirtualCardFeeOperation::Withdrawal->value);
        $this->validateAmountRange($feeSetting, $amount);
        $fee   = $feeSetting ? $feeSetting->calculateFee($amount) : 0.0;
        $total = $amount + $fee;

        $provider = $this->providerFactory->getProvider($virtualCard->provider->code);

        // Guard against over-withdrawal at the manager level so the
        // provider call is never made with an amount larger than what
        // the card actually has on file.
        $meta           = is_array($virtualCard->meta) ? $virtualCard->meta : [];
        $currentBalance = (float) ($meta['balance'] ?? 0);
        if ($amount > $currentBalance) {
            throw new NotifyErrorException(__('Withdraw amount exceeds the card balance.'));
        }

        $withdrawResponse = $provider->withdrawFromCard($amount, $virtualCard->provider_card_id);

        // Decrement the on-card balance so the dashboard reflects the
        // withdrawal immediately.
        $meta['balance'] = round(max(0, $currentBalance - (float) $amount), 2);
        $virtualCard->update(['meta' => $meta]);

        $details = [
            'trxData'       => $withdrawResponse,
            'amount'        => $amount,
            'charge'        => $fee,
            'netAmount'     => $amount,
            'payableAmount' => $total,
        ];
        $this->createTransactionData($details, $virtualCard->provider, $wallet, TrxType::CARD_WITHDRAW);

        return 'success';
    }

    /**
     * Create transaction data for logging and further processing.
     */
    protected function createTransactionData(array $details, VirtualCardProvider $provider, Wallet $wallet, TrxType $trxType)
    {
        $trxData = new TransactionData(
            user_id: Auth::id(),
            trx_type: $trxType,
            amount: $details['amount'],
            amount_flow: $trxType === TrxType::CARD_TOPUP ? AmountFlow::PLUS : AmountFlow::MINUS,
            fee: $details['charge'],
            provider: $provider->name,
            processing_type: MethodType::AUTOMATIC,
            net_amount: $details['netAmount'],
            payable_amount: $details['payableAmount'],
            payable_currency: $wallet->currency->code, // Use wallet's currency, not provider's
            wallet_reference: $wallet->uuid,
            trx_data: $details['trxData'] ?? null,
            description: __(':type via :method', ['type' => $trxType->value, 'method' => $provider->name]),
            status: TrxStatus::PENDING
        );
        Transaction::create($trxData);
    }

    /**
     * Get the fee setting for the operation (topup/withdraw).
     */
    private function getFeeSetting(VirtualCard $virtualCard, string $operation): ?VirtualCardFeeSetting
    {
        return VirtualCardFeeSetting::where('provider_id', $virtualCard->provider_id)
            ->where('currency_id', $virtualCard->wallet->currency_id)
            ->where('operation', $operation)
            ->first();
    }

    /**
     * Validate if the amount is within the allowed range.
     *
     * @throws NotifyErrorException
     */
    private function validateAmountRange(?VirtualCardFeeSetting $feeSetting, float $amount): void
    {
        if ($feeSetting) {
            $min = (float) ($feeSetting->min_amount ?? 0);
            $max = $feeSetting->max_amount !== null ? (float) $feeSetting->max_amount : INF;
            if ($amount < $min || $amount > $max) {
                throw new NotifyErrorException(__('Amount must be between :min and :max', [
                    'min' => $min,
                    'max' => $feeSetting->max_amount !== null ? $feeSetting->max_amount : __('no limit'),
                ]));
            }
        }
    }
}
