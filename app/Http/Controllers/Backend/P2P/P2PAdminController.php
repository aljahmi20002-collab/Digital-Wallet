<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\P2P;

use App\Enums\P2P\DisputeStatus;
use App\Enums\P2P\OfferStatus;
use App\Enums\P2P\OrderStatus;
use App\Http\Controllers\Backend\BaseController;
use App\Models\P2P\Dispute;
use App\Models\P2P\Offer;
use App\Models\P2P\Order;
use App\Models\P2P\P2PSetting;
use Carbon\Carbon;
use Illuminate\View\View;

class P2PAdminController extends BaseController
{
    // region Marketplace Dashboard and Global Status

    public static function permissions(): array
    {
        return [
            'index' => 'p2p-manage',
        ];
    }

    public function index(): View
    {
        $settings = P2PSetting::query()->first() ?? P2PSetting::query()->create([]);

        $days       = 14;
        $to         = now()->endOfDay();
        $from       = now()->subDays($days - 1)->startOfDay();
        $todayStart = now()->startOfDay();

        $orderCounts = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $disputeCounts = Dispute::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $labels   = [];
        $orders   = [];
        $disputes = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::parse($from)->addDays($i);
            $key  = $date->format('Y-m-d');

            $labels[]   = $date->format('M d');
            $orders[]   = (int) ($orderCounts[$key] ?? 0);
            $disputes[] = (int) ($disputeCounts[$key] ?? 0);
        }

        $activityChart = [
            'labels'   => $labels,
            'orders'   => $orders,
            'disputes' => $disputes,
        ];

        $insights = [
            'orders_today' => (int) Order::query()
                ->where('created_at', '>=', $todayStart)
                ->count(),
            'open_disputes' => (int) Dispute::query()
                ->where('status', DisputeStatus::OPEN->value)
                ->count(),
            'active_offers' => (int) Offer::query()
                ->where('status', OfferStatus::ACTIVE->value)
                ->count(),
            'in_flight_orders' => (int) Order::query()
                ->whereIn('status', [
                    OrderStatus::PENDING->value,
                    OrderStatus::PAID->value,
                    OrderStatus::DISPUTED->value,
                ])
                ->count(),
        ];

        return view('backend.p2p.marketplace_dashboard', compact('activityChart', 'settings', 'insights'));
    }

    // endregion
}
