<?php

declare(strict_types=1);

namespace App\Services\P2P;

use App\Data\TransactionData;
use App\Enums\AmountFlow;
use App\Enums\MethodType;
use App\Enums\P2P\OfferStatus;
use App\Enums\P2P\OrderSide;
use App\Enums\P2P\OrderStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Exceptions\NotifyErrorException;
use App\Models\P2P\Offer;
use App\Models\P2P\Order;
use App\Models\P2P\PaymentAccount;
use App\Models\P2P\PaymentMethod;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\P2P\P2POrderStatusNotification;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

class P2POrderService
{
    public function __construct(
        protected WalletService $wallets,
        protected TransactionService $transactions,
    ) {}

    /**
     * Create an order from an offer and hold escrow (SELL side only for now).
     *
     * @throws NotifyErrorException
     */
    public function createFromOffer(
        Offer $offer,
        int $takerId,
        float $amount,
        int $paymentMethodId,
        ?int $payerPaymentAccountId = null,
        ?int $receiverPaymentAccountId = null,
        ?string $remarks = null,
    ): Order {
        if ($amount <= 0) {
            throw new NotifyErrorException(__('Invalid amount.'));
        }

        return DB::transaction(function () use ($offer, $takerId, $amount, $paymentMethodId, $payerPaymentAccountId, $receiverPaymentAccountId, $remarks) {
            $offer = Offer::query()
                ->with(['wallet.currency', 'paymentMethods'])
                ->lockForUpdate()
                ->findOrFail($offer->id);

            $this->assertOfferCanCreateOrder($offer, $takerId, $amount);

            $paymentMethod = PaymentMethod::query()
                ->whereKey($paymentMethodId)
                ->where('status', true)
                ->lockForUpdate()
                ->first();

            if (! $paymentMethod) {
                throw new NotifyErrorException(__('The selected payment method is not available right now.'));
            }

            if ($offer->paymentMethods->isNotEmpty() && ! $offer->paymentMethods->contains('id', $paymentMethod->id)) {
                throw new NotifyErrorException(
                    $offer->side === OrderSide::SELL
                        ? __('This seller does not accept the selected payment method.')
                        : __('This buyer does not support the selected payment method.')
                );
            }

            $payerAccount    = $this->lockPaymentAccount($payerPaymentAccountId);
            $receiverAccount = $this->lockPaymentAccount($receiverPaymentAccountId);

            if ($offer->side === OrderSide::SELL) {
                if (! $payerAccount || (int) $payerAccount->user_id !== $takerId) {
                    throw new NotifyErrorException(__('Invalid payment account selected for this trade.'));
                }

                if (! $receiverAccount || (int) $receiverAccount->user_id !== (int) $offer->user_id) {
                    throw new NotifyErrorException(__('The seller payment details for this method are not available anymore.'));
                }
            } else {
                if (! $receiverAccount || (int) $receiverAccount->user_id !== $takerId) {
                    throw new NotifyErrorException(__('Invalid payment account selected for this trade.'));
                }

                // Mirror SELL-side strictness: require a maker payer account whenever the
                // offer pins a whitelist of payment methods. When the BUY offer is wide
                // open (no payment methods configured), the maker may not have a saved
                // payer account and the order proceeds without one.
                if ($offer->paymentMethods->isNotEmpty()) {
                    if (! $payerAccount || (int) $payerAccount->user_id !== (int) $offer->user_id) {
                        throw new NotifyErrorException(__('The buyer payment account for this method is not available anymore.'));
                    }
                } elseif ($payerAccount && (int) $payerAccount->user_id !== (int) $offer->user_id) {
                    throw new NotifyErrorException(__('The buyer payment account for this method is not available anymore.'));
                }
            }

            foreach ([$payerAccount, $receiverAccount] as $account) {
                if ($account && (int) $account->payment_method_id !== (int) $paymentMethod->id) {
                    throw new NotifyErrorException(__('The selected payment account does not match the payment method.'));
                }
            }

            $makerPct     = (float) setting('p2p_maker_fee_pct', 0.0);
            $takerPct     = (float) setting('p2p_taker_fee_pct', 0.0);
            $makerFee     = round($amount * ($makerPct / 100), 8);
            $takerFee     = round($amount * ($takerPct / 100), 8);
            $window       = (int) ($offer->payment_window_minutes ?: (int) setting('p2p_order_expiry_minutes', 45));
            $expiresAt    = now()->addMinutes($window);
            $currencyCode = (string) $offer->wallet->currency->code;
            $currencyId   = (int) $offer->wallet->currency_id;
            $sellerUserId = $offer->side === OrderSide::SELL ? $offer->user_id : $takerId;

            if ($offer->side === OrderSide::SELL) {
                $sellerWallet = Wallet::query()
                    ->with('currency')
                    ->whereKey($offer->wallet_id)
                    ->where('user_id', (int) $offer->user_id)
                    ->where('status', true)
                    ->lockForUpdate()
                    ->first();

                if (! $sellerWallet) {
                    throw new NotifyErrorException(__('Seller wallet not found for currency :code', ['code' => $currencyCode]));
                }
            } else {
                $sellerWallet = Wallet::query()
                    ->with('currency')
                    ->where('user_id', $takerId)
                    ->where('currency_id', $currencyId)
                    ->where('status', true)
                    ->lockForUpdate()
                    ->first();

                if (! $sellerWallet) {
                    throw new NotifyErrorException(__('Seller wallet not found for currency :code', ['code' => $currencyCode]));
                }
            }

            $sellerFee = $offer->side === OrderSide::SELL ? $makerFee : $takerFee;
            $hold      = round($amount + $sellerFee, 8);

            $debited = Wallet::query()
                ->whereKey($sellerWallet->id)
                ->where('balance', '>=', $hold)
                ->decrement('balance', $hold);

            if ($debited !== 1) {
                throw new NotifyErrorException(__('Insufficient balance for escrow.'));
            }

            $order = Order::create([
                'offer_id'                          => $offer->id,
                'maker_id'                          => $offer->user_id,
                'taker_id'                          => $takerId,
                'wallet_id'                         => $sellerWallet->id,
                'payment_method_id'                 => (int) $paymentMethod->id,
                'payer_payment_account_id'          => $payerAccount?->id,
                'receiver_payment_account_id'       => $receiverAccount?->id,
                'payer_payment_account_snapshot'    => $payerAccount?->toTradeSnapshot(),
                'receiver_payment_account_snapshot' => $receiverAccount?->toTradeSnapshot(),
                'price'                             => $offer->price,
                'amount'                            => $amount,
                'maker_fee'                         => $makerFee,
                'taker_fee'                         => $takerFee,
                'total'                             => round(((float) $offer->price * $amount), 8),
                'status'                            => OrderStatus::PENDING,
                'expires_at'                        => $expiresAt,
                'remarks'                           => $remarks,
            ]);

            $trx = $this->transactions->create(new TransactionData(
                user_id: $sellerUserId,
                trx_type: TrxType::P2P_ESCROW,
                amount: $hold,
                amount_flow: AmountFlow::MINUS,
                fee: $sellerFee,
                currency: $sellerWallet->currency->code,
                provider: 'p2p',
                processing_type: MethodType::MANUAL,
                net_amount: $amount, // escrow principal
                payable_amount: $hold,
                payable_currency: $sellerWallet->currency->code,
                wallet_reference: $sellerWallet->uuid,
                trx_reference: 'p2p_order_'.$order->id,
                trx_data: [
                    'order_id'  => $order->id,
                    'offer_id'  => $offer->id,
                    'side'      => $offer->side->value,
                    'maker_fee' => $makerFee,
                    'taker_fee' => $takerFee,
                ],
                remarks: __('P2P escrow hold for order #:id', ['id' => $order->id]),
                description: 'p2p escrow',
                status: TrxStatus::COMPLETED,
            ));

            $order->update(['trx_id' => $trx->trx_id]);

            $maker = User::find($offer->user_id);
            $taker = User::find($takerId);
            if ($maker) {
                $maker->notify(new P2POrderStatusNotification(
                    orderId: (int) $order->id,
                    title: __('P2P Order Created'),
                    message: __('An order has been created for your offer (Order #:id).', ['id' => $order->id]),
                    data: ['status' => 'PENDING']
                ));
            }
            if ($taker) {
                $taker->notify(new P2POrderStatusNotification(
                    orderId: (int) $order->id,
                    title: __('P2P Order Created'),
                    message: __('Your order was created successfully (Order #:id).', ['id' => $order->id]),
                    data: ['status' => 'PENDING']
                ));
            }

            return $order->refresh();
        }, 3);
    }

    /** Mark buyer has paid (taker action). */
    public function markPaid(Order $order, int $takerId): Order
    {
        return DB::transaction(function () use ($order, $takerId) {
            $order = $this->lockOrder($order->id, ['offer']);

            $buyerId = $order->offer->side === OrderSide::SELL ? $order->taker_id : $order->maker_id;
            if ($buyerId !== $takerId) {
                throw new NotifyErrorException(__('Unauthorized action.'));
            }
            if (! in_array($order->status->value, ['PENDING'], true)) {
                throw new NotifyErrorException(__('Order cannot be marked paid.'));
            }
            if ($order->expires_at && now()->greaterThan($order->expires_at)) {
                throw new NotifyErrorException(__('This order is already expired. Please refresh the page.'));
            }

            $order->update([
                'status'  => OrderStatus::PAID,
                'paid_at' => now(),
            ]);

            $maker = User::find($order->maker_id);
            $taker = User::find($order->taker_id);
            foreach ([$maker, $taker] as $u) {
                if ($u) {
                    $u->notify(new P2POrderStatusNotification(
                        orderId: (int) $order->id,
                        title: __('Payment Marked as Paid'),
                        message: __('Order #:id has been marked as paid.', ['id' => $order->id]),
                        data: ['status' => 'PAID']
                    ));
                }
            }

            return $order->refresh();
        }, 3);
    }

    /** Release escrow to buyer (maker action). */
    public function release(Order $order, int $actorId): Order
    {
        return DB::transaction(function () use ($order, $actorId) {
            $order = $this->lockOrder($order->id, ['offer', 'wallet.currency']);
            $offer = $order->offer;

            $sellerId = $offer->side === OrderSide::SELL ? $order->maker_id : $order->taker_id;
            if ($sellerId !== $actorId) {
                throw new NotifyErrorException(__('Unauthorized action.'));
            }
            if (! in_array($order->status->value, ['PAID'], true)) {
                throw new NotifyErrorException(__('Order is not ready to release.'));
            }

            $currencyCode = (string) $order->wallet->currency->code;
            $buyerId      = $offer->side === OrderSide::SELL ? $order->taker_id : $order->maker_id;
            $buyerWallet  = Wallet::query()
                ->with('currency')
                ->where('user_id', $buyerId)
                ->where('currency_id', (int) $order->wallet->currency_id)
                ->where('status', true)
                ->lockForUpdate()
                ->first();

            if (! $buyerWallet) {
                throw new NotifyErrorException(__('Buyer wallet not found for currency :code', ['code' => $currencyCode]));
            }

            $orderAmount = $this->toMoneyFloat($order->amount);
            $buyerFee    = $this->toMoneyFloat($offer->side === OrderSide::SELL ? $order->taker_fee : $order->maker_fee);
            $netToBuyer  = max($orderAmount - $buyerFee, 0);
            $this->wallets->addMoney($buyerWallet, $netToBuyer);

            $this->transactions->create(new TransactionData(
                user_id: $buyerId,
                trx_type: TrxType::P2P_RELEASE,
                amount: $orderAmount,
                amount_flow: AmountFlow::PLUS,
                fee: $buyerFee,
                currency: $currencyCode,
                provider: 'p2p',
                processing_type: MethodType::MANUAL,
                net_amount: $netToBuyer,
                payable_amount: $netToBuyer,
                payable_currency: $currencyCode,
                wallet_reference: $buyerWallet->uuid,
                trx_reference: 'p2p_order_'.$order->id,
                trx_data: [
                    'order_id'  => $order->id,
                    'offer_id'  => $order->offer_id,
                    'buyer_fee' => $buyerFee,
                ],
                remarks: __('P2P release for order #:id', ['id' => $order->id]),
                description: 'p2p release',
                status: TrxStatus::COMPLETED,
            ));

            $order->update([
                'status'       => OrderStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $maker = User::find($order->maker_id);
            $taker = User::find($order->taker_id);
            foreach ([$maker, $taker] as $u) {
                if ($u) {
                    $u->notify(new P2POrderStatusNotification(
                        orderId: (int) $order->id,
                        title: __('Escrow Released'),
                        message: __('Order #:id has been completed and escrow released.', ['id' => $order->id]),
                        data: ['status' => 'COMPLETED']
                    ));
                }
            }

            return $order->refresh();
        });
    }

    /** Cancel order and refund escrow to maker (if held). */
    public function cancel(Order $order, int $actorId): Order
    {
        return DB::transaction(function () use ($order, $actorId) {
            $order = $this->lockOrder($order->id, ['offer', 'wallet.currency']);

            if (! in_array($actorId, [$order->maker_id, $order->taker_id], true)) {
                throw new NotifyErrorException(__('Unauthorized action.'));
            }
            if (! in_array($order->status->value, ['PENDING'], true)) {
                throw new NotifyErrorException(__('Order cannot be cancelled now.'));
            }
            if ($order->expires_at && now()->greaterThan($order->expires_at)) {
                throw new NotifyErrorException(__('This order is already expired. Please refresh the page.'));
            }

            $offer        = $order->offer;
            $sellerWallet = Wallet::query()
                ->with('currency')
                ->whereKey($order->wallet_id)
                ->where('status', true)
                ->lockForUpdate()
                ->first();

            if (! $sellerWallet) {
                throw new NotifyErrorException(__('Seller wallet not found for currency :code', ['code' => $order->wallet->currency->code]));
            }

            $orderAmount = $this->toMoneyFloat($order->amount);
            $sellerId    = $offer->side === OrderSide::SELL ? $order->maker_id : $order->taker_id;
            $sellerFee   = $this->toMoneyFloat($offer->side === OrderSide::SELL ? $order->maker_fee : $order->taker_fee);
            $refund      = $orderAmount + $sellerFee;
            $this->wallets->addMoney($sellerWallet, $refund);

            $trxData = [
                'order_id' => $order->id,
                'offer_id' => $order->offer_id,
                'refund'   => true,
            ];
            if ($offer->side === OrderSide::SELL) {
                $trxData['maker_fee_refund'] = $this->toMoneyFloat($order->maker_fee);
            } else {
                $trxData['taker_fee_refund'] = $this->toMoneyFloat($order->taker_fee);
            }

            $this->transactions->create(new TransactionData(
                user_id: $sellerId,
                trx_type: TrxType::P2P_REFUND,
                amount: $refund,
                amount_flow: AmountFlow::PLUS,
                fee: 0,
                currency: $sellerWallet->currency->code,
                provider: 'p2p',
                processing_type: MethodType::MANUAL,
                net_amount: $refund,
                payable_amount: $refund,
                payable_currency: $sellerWallet->currency->code,
                wallet_reference: $sellerWallet->uuid,
                trx_reference: 'p2p_order_'.$order->id,
                trx_data: $trxData,
                remarks: __('P2P escrow refund for order #:id', ['id' => $order->id]),
                description: 'p2p refund',
                status: TrxStatus::COMPLETED,
            ));

            $order->update([
                'status'       => OrderStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            $maker = User::find($order->maker_id);
            $taker = User::find($order->taker_id);
            foreach ([$maker, $taker] as $u) {
                if ($u) {
                    $u->notify(new P2POrderStatusNotification(
                        orderId: (int) $order->id,
                        title: __('Order Cancelled'),
                        message: __('Order #:id has been cancelled.', ['id' => $order->id]),
                        data: ['status' => 'CANCELLED']
                    ));
                }
            }

            return $order->refresh();
        });
    }

    /** Admin resolves dispute by releasing escrow to buyer. */
    public function adminResolveRelease(Order $order): Order
    {
        if (! in_array($order->status->value, ['PAID', 'DISPUTED'])) {
            throw new NotifyErrorException(__('Order is not eligible for admin release.'));
        }

        if ($order->status === OrderStatus::PAID) {
            // Determine seller (maker for SELL, taker for BUY) and reuse release flow
            $sellerId = $order->offer->side === OrderSide::SELL ? $order->maker_id : $order->taker_id;

            return $this->release($order, $sellerId);
        }

        return DB::transaction(function () use ($order) {
            $order = $this->lockOrder($order->id, ['offer', 'wallet.currency']);
            if ($order->status !== OrderStatus::DISPUTED) {
                throw new NotifyErrorException(__('Order is not eligible for admin release.'));
            }

            $offer = $order->offer;

            $currencyCode = (string) $order->wallet->currency->code;
            $buyerId      = $offer->side === OrderSide::SELL ? $order->taker_id : $order->maker_id;
            $buyerWallet  = Wallet::query()
                ->with('currency')
                ->where('user_id', $buyerId)
                ->where('currency_id', (int) $order->wallet->currency_id)
                ->where('status', true)
                ->lockForUpdate()
                ->first();

            if (! $buyerWallet) {
                throw new NotifyErrorException(__('Buyer wallet not found for currency :code', ['code' => $currencyCode]));
            }

            $orderAmount = $this->toMoneyFloat($order->amount);
            $buyerFee    = $this->toMoneyFloat($offer->side === OrderSide::SELL ? $order->taker_fee : $order->maker_fee);
            $netToBuyer  = max($orderAmount - $buyerFee, 0);
            $this->wallets->addMoney($buyerWallet, $netToBuyer);

            $this->transactions->create(new TransactionData(
                user_id: $buyerId,
                trx_type: TrxType::P2P_RELEASE,
                amount: $orderAmount,
                amount_flow: AmountFlow::PLUS,
                fee: $buyerFee,
                currency: $currencyCode,
                provider: 'p2p',
                processing_type: MethodType::MANUAL,
                net_amount: $netToBuyer,
                payable_amount: $netToBuyer,
                payable_currency: $currencyCode,
                wallet_reference: $buyerWallet->uuid,
                trx_reference: 'p2p_order_'.$order->id,
                trx_data: [
                    'order_id'  => $order->id,
                    'offer_id'  => $order->offer_id,
                    'buyer_fee' => $buyerFee,
                    'admin'     => true,
                ],
                remarks: __('P2P release (admin) for order #:id', ['id' => $order->id]),
                description: 'p2p release',
                status: TrxStatus::COMPLETED,
            ));

            $order->update([
                'status'       => OrderStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            // Notify both parties
            $maker = User::find($order->maker_id);
            $taker = User::find($order->taker_id);
            foreach ([$maker, $taker] as $u) {
                if ($u) {
                    $u->notify(new P2POrderStatusNotification(
                        orderId: (int) $order->id,
                        title: __('Escrow Released'),
                        message: __('Order #:id has been completed and escrow released.', ['id' => $order->id]),
                        data: ['status' => 'COMPLETED']
                    ));
                }
            }

            return $order->refresh();
        });
    }

    /** Admin resolves dispute by refunding escrow to maker. */
    public function adminResolveRefund(Order $order): Order
    {
        if (! in_array($order->status->value, ['PENDING', 'PAID', 'DISPUTED'])) {
            throw new NotifyErrorException(__('Order is not eligible for admin refund.'));
        }

        $order->loadMissing(['offer', 'wallet.currency']);

        if ($order->status === OrderStatus::PENDING) {
            // Determine seller and reuse cancel flow
            $sellerId = $order->offer->side === OrderSide::SELL ? $order->maker_id : $order->taker_id;

            return $this->cancel($order, $sellerId);
        }

        return DB::transaction(function () use ($order) {
            $order = $this->lockOrder($order->id, ['offer', 'wallet.currency']);
            if (! in_array($order->status->value, ['PAID', 'DISPUTED'], true)) {
                throw new NotifyErrorException(__('Order is not eligible for admin refund.'));
            }

            $offer        = $order->offer;
            $sellerWallet = Wallet::query()
                ->with('currency')
                ->whereKey($order->wallet_id)
                ->where('status', true)
                ->lockForUpdate()
                ->first();

            if (! $sellerWallet) {
                throw new NotifyErrorException(__('Seller wallet not found for currency :code', ['code' => $order->wallet->currency->code]));
            }

            $orderAmount = $this->toMoneyFloat($order->amount);
            $sellerId    = $offer->side === OrderSide::SELL ? $order->maker_id : $order->taker_id;
            $sellerFee   = $this->toMoneyFloat($offer->side === OrderSide::SELL ? $order->maker_fee : $order->taker_fee);
            $refund      = $orderAmount + $sellerFee;
            $this->wallets->addMoney($sellerWallet, $refund);

            $this->transactions->create(new TransactionData(
                user_id: $sellerId,
                trx_type: TrxType::P2P_REFUND,
                amount: $refund,
                amount_flow: AmountFlow::PLUS,
                fee: 0,
                currency: $sellerWallet->currency->code,
                provider: 'p2p',
                processing_type: MethodType::MANUAL,
                net_amount: $refund,
                payable_amount: $refund,
                payable_currency: $sellerWallet->currency->code,
                wallet_reference: $sellerWallet->uuid,
                trx_reference: 'p2p_order_'.$order->id,
                trx_data: [
                    'order_id' => $order->id,
                    'offer_id' => $order->offer_id,
                    'refund'   => true,
                    'admin'    => true,
                ],
                remarks: __('P2P escrow refund (admin) for order #:id', ['id' => $order->id]),
                description: 'p2p refund',
                status: TrxStatus::COMPLETED,
            ));

            $order->update([
                'status'       => OrderStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Notify both parties
            $maker = User::find($order->maker_id);
            $taker = User::find($order->taker_id);
            foreach ([$maker, $taker] as $u) {
                if ($u) {
                    $u->notify(new P2POrderStatusNotification(
                        orderId: (int) $order->id,
                        title: __('Order Cancelled'),
                        message: __('Order #:id has been cancelled.', ['id' => $order->id]),
                        data: ['status' => 'CANCELLED']
                    ));
                }
            }

            return $order->refresh();
        });
    }

    /** System expires an order and refunds escrow to maker (if SELL). */
    public function expire(Order $order): Order
    {
        if (! in_array($order->status->value, ['PENDING'])) {
            return $order; // ignore if not pending
        }

        return DB::transaction(function () use ($order) {
            $order = $this->lockOrder($order->id, ['offer', 'wallet.currency']);
            if (! in_array($order->status->value, ['PENDING'], true)) {
                return $order;
            }

            $offer        = $order->offer;
            $sellerWallet = Wallet::query()
                ->with('currency')
                ->whereKey($order->wallet_id)
                ->where('status', true)
                ->lockForUpdate()
                ->first();

            if (! $sellerWallet) {
                throw new NotifyErrorException(__('Seller wallet not found for currency :code', ['code' => $order->wallet->currency->code]));
            }

            $orderAmount = $this->toMoneyFloat($order->amount);
            $sellerId    = $offer->side === OrderSide::SELL ? $order->maker_id : $order->taker_id;
            $sellerFee   = $this->toMoneyFloat($offer->side === OrderSide::SELL ? $order->maker_fee : $order->taker_fee);
            $refund      = $orderAmount + $sellerFee;
            $this->wallets->addMoney($sellerWallet, $refund);

            $trxData = [
                'order_id' => $order->id,
                'offer_id' => $order->offer_id,
                'expired'  => true,
            ];
            if ($offer->side === OrderSide::SELL) {
                $trxData['maker_fee_refund'] = $this->toMoneyFloat($order->maker_fee);
            } else {
                $trxData['taker_fee_refund'] = $this->toMoneyFloat($order->taker_fee);
            }

            $this->transactions->create(new TransactionData(
                user_id: $sellerId,
                trx_type: TrxType::P2P_REFUND,
                amount: $refund,
                amount_flow: AmountFlow::PLUS,
                fee: 0,
                currency: $sellerWallet->currency->code,
                provider: 'p2p',
                processing_type: MethodType::MANUAL,
                net_amount: $refund,
                payable_amount: $refund,
                payable_currency: $sellerWallet->currency->code,
                wallet_reference: $sellerWallet->uuid,
                trx_reference: 'p2p_order_'.$order->id,
                trx_data: $trxData,
                remarks: __('P2P escrow refund (expired) for order #:id', ['id' => $order->id]),
                description: 'p2p expire refund',
                status: TrxStatus::COMPLETED,
            ));

            $order->update([
                'status'     => OrderStatus::EXPIRED,
                'expired_at' => now(),
            ]);

            $maker = User::find($order->maker_id);
            $taker = User::find($order->taker_id);
            foreach ([$maker, $taker] as $u) {
                if ($u) {
                    $u->notify(new P2POrderStatusNotification(
                        orderId: (int) $order->id,
                        title: __('Order Expired'),
                        message: __('Order #:id has expired.', ['id' => $order->id]),
                        data: ['status' => 'EXPIRED']
                    ));
                }
            }

            return $order->refresh();
        });
    }

    private function assertOfferCanCreateOrder(Offer $offer, int $takerId, float $amount): void
    {
        if ($offer->status !== OfferStatus::ACTIVE) {
            throw new NotifyErrorException(__('This offer is not available right now.'));
        }
        if ($amount < (float) $offer->min_amount) {
            throw new NotifyErrorException(__('Amount below offer minimum.'));
        }
        if (! is_null($offer->max_amount) && $amount > (float) $offer->max_amount) {
            throw new NotifyErrorException(__('Amount exceeds offer maximum.'));
        }
        if ((int) $offer->user_id === $takerId) {
            throw new NotifyErrorException(__('You cannot trade on your own offer.'));
        }

        $taker = User::query()->find($takerId);

        if ($taker && $taker->isP2pTradingSuspended()) {
            throw new NotifyErrorException(__('Your P2P trading privileges are suspended. Please contact support.'));
        }

        $maker = $offer->user_id ? User::query()->find($offer->user_id) : null;

        if ($maker && $maker->isP2pTradingSuspended()) {
            throw new NotifyErrorException(__('This offer is not available right now.'));
        }

        $globalMin = (float) (setting('p2p_min_amount', 0) ?: 0);
        $globalMax = setting('p2p_max_amount');

        if ($globalMin > 0 && $amount < $globalMin) {
            throw new NotifyErrorException(__('Amount below system minimum.'));
        }
        if ($globalMax !== null && $globalMax !== '' && (float) $globalMax > 0 && $amount > (float) $globalMax) {
            throw new NotifyErrorException(__('Amount exceeds system maximum.'));
        }
    }

    private function lockOrder(int $orderId, array $relations = []): Order
    {
        return Order::query()
            ->with($relations)
            ->lockForUpdate()
            ->findOrFail($orderId);
    }

    private function lockPaymentAccount(?int $paymentAccountId): ?PaymentAccount
    {
        if (! $paymentAccountId) {
            return null;
        }

        return PaymentAccount::query()
            ->with('paymentMethod')
            ->whereKey($paymentAccountId)
            ->lockForUpdate()
            ->first();
    }

    private function toMoneyFloat(float|int|string|null $value): float
    {
        return round((float) ($value ?? 0), 8);
    }
}
