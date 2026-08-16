<?php

namespace App\Http\Controllers\Backend;

use App\Enums\WalletEarnPayoutFrequency;
use App\Enums\WalletEarnProfitType;
use App\Http\Requests\Backend\WalletEarnPlanRequest;
use App\Models\Currency;
use App\Models\WalletEarnPlan;
use App\Traits\FileManageTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletEarnPlanController extends BaseController
{
    use FileManageTrait;

    public static function permissions(): array
    {
        return [
            'index'                            => 'wallet-earn-list',
            'positionUpdate'                   => 'wallet-earn-manage',
            'create|store|edit|update|destroy' => 'wallet-earn-manage',
        ];
    }

    public function index(): View
    {
        $plans = WalletEarnPlan::query()
            ->withCount('stakes')
            ->with('currency')
            ->orderByDesc('is_featured')
            ->orderByRaw("CASE WHEN plan_badge IS NOT NULL AND plan_badge <> '' THEN 1 ELSE 0 END DESC")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('backend.wallet_earn.plans.index', compact('plans'));
    }

    public function positionUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'positions'         => ['required', 'array'],
            'positions.*.id'    => ['required', 'integer', 'exists:wallet_earn_plans,id'],
            'positions.*.order' => ['required', 'integer', 'min:1'],
        ]);

        foreach ((array) $validated['positions'] as $item) {
            WalletEarnPlan::query()
                ->where('id', (int) $item['id'])
                ->update(['sort_order' => (int) $item['order']]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('Plan order updated successfully.'),
        ]);
    }

    public function create(): View
    {
        return view('backend.wallet_earn.plans.create', $this->formData(new WalletEarnPlan));
    }

    public function store(WalletEarnPlanRequest $request): RedirectResponse
    {
        WalletEarnPlan::query()->create($this->planData($request, true));

        notifyEvs('success', __('Wallet Earn plan created successfully.'));

        return redirect()->route('admin.wallet-earn.plans.index');
    }

    public function edit(WalletEarnPlan $plan): View
    {
        return view('backend.wallet_earn.plans.edit', $this->formData($plan));
    }

    public function update(WalletEarnPlanRequest $request, WalletEarnPlan $plan): RedirectResponse
    {
        $plan->update($this->planData($request));

        notifyEvs('success', __('Wallet Earn plan updated successfully.'));

        return redirect()->route('admin.wallet-earn.plans.index');
    }

    public function destroy(WalletEarnPlan $plan): RedirectResponse
    {
        $this->delete($plan->icon);
        $plan->delete();

        notifyEvs('success', __('Wallet Earn plan deleted successfully.'));

        return redirect()->route('admin.wallet-earn.plans.index');
    }

    private function formData(WalletEarnPlan $plan): array
    {
        return [
            'plan'              => $plan,
            'currencies'        => Currency::query()->where('status', true)->orderBy('code')->get(),
            'profitTypes'       => WalletEarnProfitType::options(),
            'payoutFrequencies' => WalletEarnPayoutFrequency::options(),
            'durationUnits'     => [
                'hours'  => __('Hours'),
                'days'   => __('Days'),
                'months' => __('Months'),
            ],
        ];
    }

    private function planData(WalletEarnPlanRequest $request, bool $isCreate = false): array
    {
        $data = $request->validated();

        $data['currency_id']      = $data['currency_id'] ?? null;
        $data['return_principal'] = $request->boolean('return_principal');
        $data['auto_approve']     = $request->boolean('auto_approve');
        $data['is_featured']      = $request->boolean('is_featured');
        $data['plan_badge']       = trim((string) ($data['plan_badge'] ?? ''));
        $data['status']           = $request->boolean('status');
        $data['sort_order']       = $isCreate
            ? ((int) WalletEarnPlan::query()->max('sort_order')) + 1
            : (int) $request->integer('sort_order', (int) $request->route('plan')?->sort_order);

        if (! $isCreate && $data['sort_order'] < 1) {
            $data['sort_order'] = 1;
        }

        $existingIcon = $isCreate ? null : $request->route('plan')?->icon;

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->uploadImage($request->file('icon'), $existingIcon);
        } else {
            unset($data['icon']);
        }

        return $data;
    }
}
