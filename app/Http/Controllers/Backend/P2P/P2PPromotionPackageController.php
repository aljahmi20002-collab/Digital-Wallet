<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\P2P;

use App\Http\Controllers\Backend\BaseController;
use App\Models\P2P\PromotionPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class P2PPromotionPackageController extends BaseController
{
    // region Promotion Plan Management

    public static function permissions(): array
    {
        return [
            'index|create|store|edit|update|destroy|positionUpdate' => 'p2p-manage',
        ];
    }

    public function index(): View
    {
        $packages = PromotionPackage::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('backend.p2p.promotion_plans.plans_list', compact('packages'));
    }

    public function create(): View
    {
        $package = new PromotionPackage;

        if (old('duration_value') !== null && old('duration_unit') !== null) {
            $durationValue = (int) old('duration_value');
            $durationUnit  = (string) old('duration_unit');
        } else {
            $durationMinutes                = 1440;
            [$durationValue, $durationUnit] = $this->splitDurationForForm($durationMinutes);
        }

        return view('backend.p2p.promotion_plans.create_plan', compact('package', 'durationValue', 'durationUnit'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data               = $this->validatedPackageData($request);
        $data['sort_order'] = ((int) PromotionPackage::query()->max('sort_order')) + 1;

        PromotionPackage::query()->create($data);

        return redirect()
            ->route('admin.p2p.promotion-packages.index')
            ->with('notifyevs', ['type' => 'success', 'message' => __('Plan created')]);
    }

    public function edit(PromotionPackage $promotion_package): View
    {
        $package = $promotion_package;

        if (old('duration_value') !== null && old('duration_unit') !== null) {
            $durationValue = (int) old('duration_value');
            $durationUnit  = (string) old('duration_unit');
        } else {
            $durationMinutes                = (int) $package->duration_minutes;
            [$durationValue, $durationUnit] = $this->splitDurationForForm($durationMinutes);
        }

        return view('backend.p2p.promotion_plans.edit_plan', compact('package', 'durationValue', 'durationUnit'));
    }

    public function update(Request $request, PromotionPackage $promotion_package): RedirectResponse
    {
        $data = $this->validatedPackageData($request);

        $promotion_package->update($data);

        return redirect()
            ->route('admin.p2p.promotion-packages.index')
            ->with('notifyevs', ['type' => 'success', 'message' => __('Plan updated')]);
    }

    public function destroy(PromotionPackage $promotion_package): RedirectResponse
    {
        $promotion_package->delete();

        return redirect()
            ->route('admin.p2p.promotion-packages.index')
            ->with('notifyevs', ['type' => 'success', 'message' => __('Plan deleted')]);
    }

    public function positionUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'positions'         => 'required|array',
            'positions.*.id'    => 'required|integer|exists:p2p_promotion_packages,id',
            'positions.*.order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ((array) $validated['positions'] as $item) {
                PromotionPackage::query()
                    ->where('id', (int) $item['id'])
                    ->update(['sort_order' => (int) $item['order']]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => __('Plan order updated successfully.'),
        ]);
    }

    // endregion

    // region Private Helpers

    private function validatedPackageData(Request $request): array
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'visibility' => 'required|in:PUBLIC,HIDDEN,INTERNAL',
            'status'     => 'nullable|boolean',

            'billing_type'       => 'required|in:FIXED,DAILY_PRICE,PER_TRADE_FEE',
            'price'              => 'exclude_unless:billing_type,FIXED|required|numeric|min:0.00000001',
            'daily_price'        => 'exclude_unless:billing_type,DAILY_PRICE|required|numeric|min:0.00000001',
            'per_trade_fee'      => 'exclude_unless:billing_type,PER_TRADE_FEE|required|numeric|min:0.00000001',
            'auto_renew_allowed' => 'nullable|boolean',

            'duration_value' => 'required|integer|min:1|max:525600',
            'duration_unit'  => 'required|in:MINUTES,HOURS,DAYS',

            'features'        => 'nullable|array',
            'accent_color'    => 'nullable|in:GOLD,BLUE,RED',
            'search_priority' => 'nullable|integer|min:0|max:1000000',

            'applies_to'           => 'required|in:BUY,SELL,BOTH',
            'allowed_categories'   => 'nullable|array',
            'allowed_categories.*' => 'in:CRYPTO,GIFT_CARD,LOCAL_PAYMENT,ALL',

            'max_active_per_user'           => 'nullable|integer|min:1|max:1000000',
            'cooldown_after_expiry_minutes' => 'nullable|integer|min:0|max:525600',
        ]);

        $durationMinutes = $this->durationMinutesFromForm(
            (int) $validated['duration_value'],
            (string) $validated['duration_unit'],
        );

        if ($durationMinutes > 525600) {
            throw ValidationException::withMessages([
                'duration_value' => __('Duration is too large.'),
            ]);
        }

        $rawFeatures = (array) ($validated['features'] ?? []);
        $features    = [];
        foreach ($rawFeatures as $key => $value) {
            if ((bool) $value) {
                $features[(string) $key] = true;
            }
        }

        if (! empty($features['verified_badge']) && empty($features['featured_badge'])) {
            $features['featured_badge'] = true;
        }

        // Strip legacy / dead feature flags so only flags actually rendered downstream are persisted.
        unset(
            $features['verified_badge'],
            $features['sticky_placement'],
            $features['homepage_banner'],
            $features['featured_listing'],
            $features['search_priority_boost'],
        );

        $allowedCategories = array_values(array_unique((array) ($validated['allowed_categories'] ?? [])));
        if (in_array('ALL', $allowedCategories, true)) {
            $allowedCategories = ['ALL'];
        }

        $billingType = (string) $validated['billing_type'];
        $price       = $billingType === 'FIXED' ? (float) $validated['price'] : 0.0;
        $dailyPrice  = $billingType === 'DAILY_PRICE' ? (float) $validated['daily_price'] : null;
        $perTradeFee = $billingType === 'PER_TRADE_FEE' ? (float) $validated['per_trade_fee'] : null;

        $baseCurrency = (string) (siteCurrency('code') ?: config('app.default_currency'));
        $baseCurrency = strtoupper(trim($baseCurrency));

        return [
            'name'                          => trim((string) $validated['name']),
            'price'                         => $price,
            'base_currency'                 => $baseCurrency,
            'duration_minutes'              => $durationMinutes,
            'visibility'                    => (string) $validated['visibility'],
            'billing_type'                  => $billingType,
            'daily_price'                   => $dailyPrice,
            'per_trade_fee'                 => $perTradeFee,
            'auto_renew_allowed'            => (bool) ($validated['auto_renew_allowed'] ?? false),
            'features'                      => $features !== [] ? $features : null,
            'accent_color'                  => $validated['accent_color'] ?? null,
            'search_priority'               => (int) ($validated['search_priority'] ?? 0),
            'applies_to'                    => (string) $validated['applies_to'],
            'allowed_categories'            => $allowedCategories !== [] ? $allowedCategories : null,
            'max_active_per_user'           => isset($validated['max_active_per_user']) ? (int) $validated['max_active_per_user'] : null,
            'cooldown_after_expiry_minutes' => isset($validated['cooldown_after_expiry_minutes']) ? (int) $validated['cooldown_after_expiry_minutes'] : null,
            'status'                        => (bool) ($validated['status'] ?? true),
        ];
    }

    private function durationMinutesFromForm(int $value, string $unit): int
    {
        $value = max(1, $value);

        return match ($unit) {
            'DAYS'  => $value * 1440,
            'HOURS' => $value * 60,
            default => $value,
        };
    }

    private function splitDurationForForm(int $durationMinutes): array
    {
        $durationMinutes = max(1, $durationMinutes);

        if ($durationMinutes % 1440 === 0) {
            return [(int) ($durationMinutes / 1440), 'DAYS'];
        }

        if ($durationMinutes % 60 === 0) {
            return [(int) ($durationMinutes / 60), 'HOURS'];
        }

        return [$durationMinutes, 'MINUTES'];
    }

    // endregion
}
