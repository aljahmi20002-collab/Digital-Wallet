<?php

namespace App\Http\Controllers\Backend;

use App\Enums\MobileRechargeStatus;
use App\Models\MobileRecharge;
use App\Models\MobileRechargeProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MobileRechargeController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|show' => 'mobile-recharge-list',
        ];
    }

    public function index(Request $request): View
    {
        $filters = [
            'status'   => $request->string('status')->toString() ?: null,
            'provider' => $request->string('provider')->toString() ?: null,
            'search'   => $request->string('search')->toString() ?: null,
        ];

        $recharges = MobileRecharge::query()
            ->with(['user', 'wallet.currency', 'transaction'])
            ->when($filters['status'], fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['provider'], fn ($query, string $provider) => $query->where('provider', $provider))
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('phone_number', 'like', "%{$search}%")
                        ->orWhere('operator', 'like', "%{$search}%")
                        ->orWhere('provider_reference', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $metrics = [
            'total'      => MobileRecharge::query()->count(),
            'completed'  => MobileRecharge::query()->where('status', MobileRechargeStatus::COMPLETED)->count(),
            'processing' => MobileRecharge::query()->whereIn('status', [MobileRechargeStatus::PENDING, MobileRechargeStatus::PROCESSING])->count(),
            'failed'     => MobileRecharge::query()->where('status', MobileRechargeStatus::FAILED)->count(),
            'volume'     => (float) MobileRecharge::query()->where('status', MobileRechargeStatus::COMPLETED)->sum('amount'),
        ];

        $registeredProviders = MobileRechargeProvider::query()
            ->with('plugin')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return view('backend.mobile_recharge.index', [
            'filters'              => $filters,
            'metrics'              => $metrics,
            'registeredProviders'  => $registeredProviders,
            'recharges'            => $recharges,
            'selectedProviderCode' => setting('mobile_recharge_provider', config('mobile_services.recharge.provider', 'sandbox')),
            'statuses'             => MobileRechargeStatus::cases(),
            'driverLabels'         => (array) config('mobile_services.recharge.driver_labels', []),
            'activeTab'            => $request->string('tab')->toString() ?: null,
        ]);
    }

    public function show(MobileRecharge $mobileRecharge): View
    {
        $mobileRecharge->load(['user', 'wallet.currency', 'transaction']);

        return view('backend.mobile_recharge.show', [
            'recharge' => $mobileRecharge,
        ]);
    }
}
