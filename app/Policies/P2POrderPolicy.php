<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\P2P\OrderSide;
use App\Models\P2P\Order;
use App\Models\User;

class P2POrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return in_array((int) $user->id, [(int) $order->maker_id, (int) $order->taker_id], true);
    }

    public function markPaid(User $user, Order $order): bool
    {
        return (int) $user->id === (int) $this->buyerId($order);
    }

    public function release(User $user, Order $order): bool
    {
        return (int) $user->id === (int) $this->sellerId($order);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    public function dispute(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    public function feedback(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    private function buyerId(Order $order): int
    {
        return $order->offer->side === OrderSide::SELL
            ? (int) $order->taker_id
            : (int) $order->maker_id;
    }

    private function sellerId(Order $order): int
    {
        return $order->offer->side === OrderSide::SELL
            ? (int) $order->maker_id
            : (int) $order->taker_id;
    }
}
