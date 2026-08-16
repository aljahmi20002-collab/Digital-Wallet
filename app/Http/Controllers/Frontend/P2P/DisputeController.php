<?php

namespace App\Http\Controllers\Frontend\P2P;

use App\Enums\P2P\DisputeStatus;
use App\Enums\P2P\OrderStatus;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\P2P\StoreDisputeRequest;
use App\Models\P2P\Dispute;
use App\Models\P2P\Order;
use App\Models\User;
use App\Notifications\P2P\P2POrderStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    public function store(StoreDisputeRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('dispute', $order);

        if (! in_array($order->status->value, ['PENDING', 'PAID'], true)) {
            throw new NotifyErrorException(__('This order cannot be disputed now.'));
        }

        if ($order->status === OrderStatus::PENDING && $order->expires_at && now()->greaterThan($order->expires_at)) {
            throw new NotifyErrorException(__('This order is already expired. Please refresh the page.'));
        }

        $windowMinutes = (int) setting('p2p_dispute_window_minutes', 120);
        $baseTime      = $order->paid_at ?? $order->created_at;

        if ($windowMinutes > 0 && $baseTime && now()->greaterThan($baseTime->copy()->addMinutes($windowMinutes))) {
            throw new NotifyErrorException(__('Dispute window has expired for this order.'));
        }

        DB::transaction(function () use ($request, $order, $windowMinutes): void {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (! in_array($order->status->value, ['PENDING', 'PAID'], true)) {
                throw new NotifyErrorException(__('This order cannot be disputed now.'));
            }

            if ($order->status === OrderStatus::PENDING && $order->expires_at && now()->greaterThan($order->expires_at)) {
                throw new NotifyErrorException(__('This order is already expired. Please refresh the page.'));
            }

            $baseTime = $order->paid_at ?? $order->created_at;

            if ($windowMinutes > 0 && $baseTime && now()->greaterThan($baseTime->copy()->addMinutes($windowMinutes))) {
                throw new NotifyErrorException(__('Dispute window has expired for this order.'));
            }

            $existingDispute = Dispute::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if ($existingDispute) {
                throw new NotifyErrorException(__('A dispute has already been opened for this trade.'));
            }

            Dispute::query()->create([
                'order_id'  => $order->id,
                'raised_by' => (int) auth()->id(),
                'status'    => DisputeStatus::OPEN,
                'reason'    => $request->string('reason')->toString(),
            ]);

            $order->update([
                'status'      => OrderStatus::DISPUTED,
                'disputed_at' => now(),
            ]);
        }, 3);

        // Notify maker and taker
        $maker = User::find($order->maker_id);
        $taker = User::find($order->taker_id);
        foreach ([$maker, $taker] as $u) {
            if ($u) {
                $u->notify(new P2POrderStatusNotification(
                    orderId: (int) $order->id,
                    title: __('Trade Dispute Opened'),
                    message: __('A trade dispute has been opened for Order #:id.', ['id' => $order->id]),
                    data: ['status' => 'DISPUTED']
                ));
            }
        }

        notifyEvs('success', __('Trade dispute opened. Support will review.'));

        return back();
    }
}
