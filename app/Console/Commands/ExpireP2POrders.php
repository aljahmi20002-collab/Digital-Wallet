<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\P2P\Order;
use App\Services\P2P\P2POrderService;
use Illuminate\Console\Command;

class ExpireP2POrders extends Command
{
    protected $signature = 'p2p:orders:expire';

    protected $description = 'Expire pending P2P orders that passed their expiry time and refund escrow if applicable';

    public function handle(P2POrderService $service): int
    {
        $now = now();
        $expired = 0;

        Order::with(['offer','wallet.currency'])
            ->where('status', 'PENDING')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->chunkById(200, function ($orders) use ($service, &$expired) {
                foreach ($orders as $order) {
                    $service->expire($order);
                    $expired++;
                }
            });

        $this->info("Expired {$expired} orders.");

        return self::SUCCESS;
    }
}
