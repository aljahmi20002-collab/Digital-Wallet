<?php

namespace App\Http\Controllers\Frontend\P2P;

use App\Enums\P2P\OrderSide;
use App\Enums\P2P\OrderStatus;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\P2P\StoreOrderRequest;
use App\Models\P2P\Offer;
use App\Models\P2P\OfferFeedback;
use App\Models\P2P\Order;
use App\Models\P2P\PaymentAccount;
use App\Services\P2P\P2POrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderController extends Controller
{
    // region Trade Order Flow

    public function __construct(protected P2POrderService $service) {}

    public function index(): View
    {
        $orders = Order::query()
            ->with(['offer', 'wallet.currency', 'feedbacks', 'paymentMethod'])
            ->where(function ($q) {
                $q->where('maker_id', auth()->id())
                    ->orWhere('taker_id', auth()->id());
            })
            ->latest()
            ->paginate(15);

        return view('frontend.user.p2p.trade_orders.trade_orders', compact('orders'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $offer = Offer::with(['wallet.currency', 'paymentMethods', 'user'])->findOrFail($validated['offer_id']);

        $selectedAccount = PaymentAccount::query()
            ->with('paymentMethod')
            ->where('id', $validated['payment_account_id'])
            ->where('user_id', auth()->id())
            ->first();

        if (! $selectedAccount) {
            notifyEvs('error', __('Invalid payment account selected for this trade.'));

            return back();
        }

        $paymentMethod = $selectedAccount->paymentMethod;

        if (! $paymentMethod || ! (bool) $paymentMethod->status) {
            notifyEvs('error', __('The selected payment method is not available right now.'));

            return back();
        }

        $payerAccount    = null;
        $receiverAccount = null;

        if ($offer->side === OrderSide::SELL) {
            $allowedMethodIds = $offer->paymentMethods
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! in_array((int) $selectedAccount->payment_method_id, $allowedMethodIds, true)) {
                notifyEvs('error', __('This seller does not accept the selected payment method.'));

                return back();
            }

            $payerAccount    = $selectedAccount;
            $receiverAccount = PaymentAccount::query()
                ->with('paymentMethod')
                ->where('user_id', (int) $offer->user_id)
                ->where('payment_method_id', (int) $selectedAccount->payment_method_id)
                ->first();

            if (! $receiverAccount) {
                notifyEvs('error', __('The seller payment details for this method are not available anymore.'));

                return back();
            }
        } else {
            $receiverAccount = $selectedAccount;

            if ($offer->paymentMethods->isNotEmpty()) {
                $allowedMethodIds = $offer->paymentMethods
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                if (! in_array((int) $selectedAccount->payment_method_id, $allowedMethodIds, true)) {
                    notifyEvs('error', __('This buyer does not support the selected payment method.'));

                    return back();
                }

                $payerAccount = PaymentAccount::query()
                    ->with('paymentMethod')
                    ->where('user_id', (int) $offer->user_id)
                    ->where('payment_method_id', (int) $selectedAccount->payment_method_id)
                    ->first();

                if (! $payerAccount) {
                    notifyEvs('error', __('The buyer payment account for this method is not available anymore.'));

                    return back();
                }
            }
        }

        $remarks = __('Payment method: :name', ['name' => (string) $paymentMethod->name]);

        try {
            $order = $this->service->createFromOffer(
                offer: $offer,
                takerId: (int) auth()->id(),
                amount: (float) $validated['amount'],
                paymentMethodId: (int) $paymentMethod->id,
                payerPaymentAccountId: $payerAccount?->id,
                receiverPaymentAccountId: $receiverAccount?->id,
                remarks: $remarks,
            );
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());

            return back();
        }

        notifyEvs('success', __('Trade order created. Complete payment within the time window.'));

        return redirect()->route('user.p2p.orders.show', [$order, 'created' => 1]);
    }

    public function show(Order $order): View
    {
        $order->load(['offer.paymentMethods', 'wallet.currency', 'maker', 'taker', 'feedbacks', 'paymentMethod', 'payerPaymentAccount.paymentMethod', 'receiverPaymentAccount.paymentMethod']);
        $this->authorize('view', $order);

        return view('frontend.user.p2p.trade_orders.trade_order_details', compact('order'));
    }

    public function feedback(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('feedback', $order);

        $validator = Validator::make($request->all(), [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('p2p_feedback_order_id', (int) $order->id);
        }

        $validated = $validator->validated();

        $actorId = (int) auth()->id();

        if ($order->status !== OrderStatus::COMPLETED) {
            notifyEvs('error', __('You can submit a review only after the trade is completed.'));

            return back();
        }

        if ($actorId !== (int) $order->taker_id) {
            notifyEvs('error', __('Only the counterparty can submit a review for this trade.'));

            return back();
        }

        $exists = OfferFeedback::query()
            ->where('order_id', $order->id)
            ->where('user_id', $actorId)
            ->exists();

        if ($exists) {
            notifyEvs('error', __('You have already submitted a review for this trade.'));

            return back();
        }

        OfferFeedback::create([
            'offer_id' => (int) $order->offer_id,
            'order_id' => (int) $order->id,
            'user_id'  => $actorId,
            'rating'   => (int) $validated['rating'],
            'comment'  => $validated['comment'] ?? null,
        ]);

        notifyEvs('success', __('Thanks! Your trade review has been submitted.'));

        return back();
    }

    public function status(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        $order->load(['offer', 'wallet.currency']);

        $sideEnum = $order->offer->side;
        $sellerId = (int) ($sideEnum === OrderSide::SELL ? $order->maker_id : $order->taker_id);
        $buyerId  = (int) ($sideEnum === OrderSide::SELL ? $order->taker_id : $order->maker_id);
        $actorId  = (int) auth()->id();
        $role     = $actorId === $sellerId ? 'seller' : ($actorId === $buyerId ? 'buyer' : 'guest');

        $isExpired = $order->status->value === 'PENDING'
            && $order->expires_at
            && now()->greaterThan($order->expires_at);

        $displayStatus = $isExpired ? OrderStatus::EXPIRED : $order->status;

        return response()->json([
            'id'                  => $order->id,
            'status'              => $order->status->value,
            'status_label'        => $order->status->label(),
            'status_badge_class'  => $order->status->badgeClass(),
            'display_status'      => $displayStatus->value,
            'display_label'       => $displayStatus->label(),
            'display_badge_class' => $displayStatus->badgeClass(),
            'side'                => $sideEnum->value,
            'role'                => $role,
            'is_expired'          => $isExpired,
            'paid_at'             => optional($order->paid_at)->toIso8601String(),
            'completed_at'        => optional($order->completed_at)->toIso8601String(),
            'cancelled_at'        => optional($order->cancelled_at)->toIso8601String(),
            'expired_at'          => optional($order->expired_at)->toIso8601String(),
            'expires_at'          => optional($order->expires_at)->toIso8601String(),
            'now'                 => now()->toIso8601String(),
            'can_mark_paid'       => $role                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   === 'buyer'  && $order->status->value === 'PENDING' && ! $isExpired,
            'can_release'         => $role                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   === 'seller' && $order->status->value === 'PAID',
            'can_cancel'          => in_array($actorId, [(int) $order->maker_id, (int) $order->taker_id], true)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           && $order->status->value === 'PENDING' && ! $isExpired,
        ]);
    }

    public function markPaid(Order $order): RedirectResponse
    {
        $order->loadMissing('offer');
        $this->authorize('markPaid', $order);
        try {
            $this->service->markPaid($order, auth()->id());
            notifyEvs('success', __('Payment marked as sent. Waiting for escrow release.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return back();
    }

    public function release(Order $order): RedirectResponse
    {
        $order->loadMissing('offer');
        $this->authorize('release', $order);
        try {
            $this->service->release($order, auth()->id());
            notifyEvs('success', __('Escrow released successfully.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return back();
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);
        try {
            $this->service->cancel($order, auth()->id());
            notifyEvs('success', __('Trade order canceled.'));
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());
        }

        return back();
    }
}
