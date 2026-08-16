<?php

declare(strict_types=1);

namespace App\Services\P2P;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\MethodType;
use App\Enums\P2P\OfferStatus;
use App\Enums\P2P\PromotionStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Models\P2P\Offer;
use App\Models\P2P\OfferPromotion;
use App\Models\P2P\OfferPromotionPurchase;
use App\Models\P2P\PromotionPackage;
use App\Models\Wallet;
use App\Services\CurrencyConversionService;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class P2POfferPromotionService
{
    public function __construct(
        protected WalletService $wallets,
        protected TransactionService $transactions,
        protected CurrencyConversionService $converter,
    ) {}

    public function quote(PromotionPackage $package, Wallet $wallet): array
    {
        $baseCurrency = (string) ($package->base_currency ?: siteCurrency());
        $paidCurrency = (string) ($wallet->currency->code ?: '');

        $decimals = (int) setting('site_decimal', 2);

        if ($paidCurrency === '') {
            throw new NotifyErrorException(__('Wallet currency not found.'));
        }

        if (! $package->status) {
            throw new NotifyErrorException(__('This promotion package is not available.'));
        }

        $visibility = strtoupper(trim((string) ($package->visibility ?? 'PUBLIC')));
        if ($visibility !== 'PUBLIC') {
            throw new NotifyErrorException(__('This promotion package is not available.'));
        }

        $basePrice = (float) $package->effectiveBasePrice();
        if ($basePrice <= 0) {
            throw new NotifyErrorException(__('Invalid promotion package price.'));
        }

        if ($baseCurrency === $paidCurrency) {
            $paidAmount   = $basePrice;
            $exchangeRate = 1.0;
        } else {
            try {
                $paidAmount = $this->converter->convertCurrency($basePrice, $baseCurrency, $paidCurrency);
            } catch (\Throwable) {
                $paidAmount = null;
            }
            if ($paidAmount === null) {
                throw new NotifyErrorException(__('Unable to convert currency right now. Please try again later.'));
            }

            try {
                $exchangeRate = $this->converter->convertCurrency(1, $baseCurrency, $paidCurrency);
            } catch (\Throwable) {
                $exchangeRate = null;
            }
            if ($exchangeRate === null) {
                $exchangeRate = 0.0;
            }
        }

        return [
            'base_currency' => $baseCurrency,
            'base_price'    => $basePrice,
            'paid_currency' => $paidCurrency,
            'paid_amount'   => (float) round((float) $paidAmount, $decimals),
            'exchange_rate' => (float) round((float) $exchangeRate, $decimals),
        ];
    }

    public function purchase(Offer $offer, PromotionPackage $package, Wallet $wallet, int $actorUserId): OfferPromotion
    {
        if ((int) $offer->user_id !== $actorUserId) {
            throw new NotifyErrorException(__('You are not allowed to promote this offer.'));
        }

        if ($offer->status === OfferStatus::DISABLED) {
            throw new NotifyErrorException(__('This offer is not available for promotion.'));
        }

        if (! $package->status) {
            throw new NotifyErrorException(__('This promotion package is not available.'));
        }

        $visibility = strtoupper(trim((string) ($package->visibility ?? 'PUBLIC')));
        if ($visibility !== 'PUBLIC') {
            throw new NotifyErrorException(__('This promotion package is not available.'));
        }

        $appliesTo = strtoupper(trim((string) ($package->applies_to ?? 'BOTH')));
        $offerSide = strtoupper(trim((string) ($offer->side?->value ?? $offer->side ?? '')));

        if ($appliesTo !== 'BOTH' && $offerSide !== '' && $appliesTo !== $offerSide) {
            throw new NotifyErrorException(__('This promotion package is not available for this offer.'));
        }

        $duration = (int) $package->duration_minutes;
        if ($duration <= 0) {
            throw new NotifyErrorException(__('Invalid promotion package duration.'));
        }

        if ((int) $wallet->user_id !== $actorUserId || ! $wallet->status) {
            throw new NotifyErrorException(__('Invalid wallet selected.'));
        }

        $quote      = $this->quote($package, $wallet);
        $paidAmount = (float) $quote['paid_amount'];

        $maxActivePerUser = (int) ($package->max_active_per_user ?? 0);
        $cooldownMinutes  = (int) ($package->cooldown_after_expiry_minutes ?? 0);

        return DB::transaction(function () use ($offer, $package, $wallet, $actorUserId, $duration, $quote, $paidAmount, $maxActivePerUser, $cooldownMinutes) {
            $promotion = OfferPromotion::query()
                ->where('offer_id', $offer->id)
                ->lockForUpdate()
                ->first();

            $now = now();

            $currentEndsAt     = $promotion?->ends_at;
            $isCurrentlyActive = $promotion
                && $promotion->status === PromotionStatus::ACTIVE
                && $currentEndsAt
                && $currentEndsAt->greaterThan($now);

            if (! $isCurrentlyActive && $cooldownMinutes > 0 && $promotion && $promotion->ends_at && $promotion->ends_at->isPast()) {
                $cooldownUntil = $promotion->ends_at->copy()->addMinutes($cooldownMinutes);
                if ($cooldownUntil->greaterThan($now)) {
                    throw new NotifyErrorException(__('Please wait before promoting again.'));
                }
            }

            if (! $isCurrentlyActive && $maxActivePerUser > 0) {
                $activeCount = OfferPromotion::query()
                    ->where('user_id', $actorUserId)
                    ->where('status', PromotionStatus::ACTIVE)
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '>', $now)
                    ->where('offer_id', '!=', $offer->id)
                    ->lockForUpdate()
                    ->count();

                if ($activeCount >= $maxActivePerUser) {
                    throw new NotifyErrorException(__('Maximum active promotions reached.'));
                }
            }

            $purchaseStartsAt = $isCurrentlyActive ? $currentEndsAt : $now;
            $purchaseEndsAt   = $purchaseStartsAt->copy()->addMinutes($duration);

            $this->wallets->subtractMoney($wallet, $paidAmount);

            $trx = $this->transactions->create(new TransactionData(
                user_id: $actorUserId,
                trx_type: TrxType::P2P_PROMOTION,
                amount: $paidAmount,
                amount_flow: AmountFlow::MINUS,
                fee: 0,
                currency: $wallet->currency->code,
                provider: 'p2p',
                processing_type: MethodType::AUTOMATIC,
                net_amount: $paidAmount,
                payable_amount: $paidAmount,
                payable_currency: $wallet->currency->code,
                wallet_reference: $wallet->uuid,
                trx_reference: 'p2p_promotion_offer_'.$offer->id,
                trx_data: [
                    'offer_id'         => (int) $offer->id,
                    'package_id'       => (int) $package->id,
                    'duration_minutes' => $duration,
                    'base_price'       => (float) $quote['base_price'],
                    'base_currency'    => (string) $quote['base_currency'],
                    'paid_amount'      => (float) $quote['paid_amount'],
                    'paid_currency'    => (string) $quote['paid_currency'],
                    'exchange_rate'    => (float) $quote['exchange_rate'],
                    'starts_at'        => $purchaseStartsAt->toDateTimeString(),
                    'ends_at'          => $purchaseEndsAt->toDateTimeString(),
                ],
                remarks: __('P2P offer promotion purchase (Offer #:id)', ['id' => (int) $offer->id]),
                description: 'p2p promotion',
                status: TrxStatus::COMPLETED,
            ));

            OfferPromotionPurchase::create([
                'offer_id'         => $offer->id,
                'user_id'          => $actorUserId,
                'package_id'       => $package->id,
                'wallet_id'        => $wallet->id,
                'trx_id'           => $trx->trx_id,
                'base_price'       => (float) $quote['base_price'],
                'base_currency'    => (string) $quote['base_currency'],
                'paid_amount'      => (float) $quote['paid_amount'],
                'paid_currency'    => (string) $quote['paid_currency'],
                'exchange_rate'    => (float) $quote['exchange_rate'],
                'duration_minutes' => $duration,
                'starts_at'        => $purchaseStartsAt,
                'ends_at'          => $purchaseEndsAt,
            ]);

            if (! $promotion) {
                $promotion = OfferPromotion::create([
                    'offer_id'      => $offer->id,
                    'user_id'       => $actorUserId,
                    'package_id'    => $package->id,
                    'wallet_id'     => $wallet->id,
                    'trx_id'        => $trx->trx_id,
                    'base_price'    => (float) $quote['base_price'],
                    'base_currency' => (string) $quote['base_currency'],
                    'paid_amount'   => (float) $quote['paid_amount'],
                    'paid_currency' => (string) $quote['paid_currency'],
                    'exchange_rate' => (float) $quote['exchange_rate'],
                    'starts_at'     => $isCurrentlyActive ? $promotion?->starts_at : $now,
                    'ends_at'       => $purchaseEndsAt,
                    'status'        => PromotionStatus::ACTIVE,
                ]);

                return $promotion;
            }

            $promotion->update([
                'user_id'       => $actorUserId,
                'package_id'    => $package->id,
                'wallet_id'     => $wallet->id,
                'trx_id'        => $trx->trx_id,
                'base_price'    => (float) $quote['base_price'],
                'base_currency' => (string) $quote['base_currency'],
                'paid_amount'   => (float) $quote['paid_amount'],
                'paid_currency' => (string) $quote['paid_currency'],
                'exchange_rate' => (float) $quote['exchange_rate'],
                'starts_at'     => $isCurrentlyActive ? $promotion->starts_at : $now,
                'ends_at'       => $purchaseEndsAt,
                'status'        => PromotionStatus::ACTIVE,
            ]);

            return $promotion->refresh();
        });
    }
}
