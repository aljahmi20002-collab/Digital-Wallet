<?php

namespace App\Http\Controllers\Backend;

use App\Enums\MerchantStatus;
use App\Http\Requests\Merchant\MerchantActionRequest;
use App\Models\Merchant;
use App\Notifications\TemplateNotification;
use Illuminate\Http\Request;

class MerchantController extends BaseController
{
    public static function permissions(): array
    {
        return [
            'index|pendingMerchant|approvedMerchant|rejectedMerchant' => 'merchant-list',
            'merchantAction'                                          => 'merchant-manage',
        ];
    }

    public function index(Request $request)
    {
        $title     = __('Merchant List');
        $merchants = Merchant::query()
            ->with(['user', 'currency', 'supportedCurrencies'])
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        return view('backend.merchant.index', compact('merchants', 'title'));
    }

    public function pendingMerchant(Request $request)
    {
        $title     = __('Pending Merchant');
        $merchants = Merchant::query()
            ->with(['user', 'currency', 'supportedCurrencies'])
            ->where('status', MerchantStatus::PENDING)
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        return view('backend.merchant.index', compact('merchants', 'title'));
    }

    public function approvedMerchant(Request $request)
    {
        $title     = __('Approved Merchant');
        $merchants = Merchant::query()
            ->with(['user', 'currency', 'supportedCurrencies'])
            ->where('status', MerchantStatus::APPROVED)
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        return view('backend.merchant.index', compact('merchants', 'title'));
    }

    public function rejectedMerchant(Request $request)
    {
        $title     = __('Rejected Merchant');
        $merchants = Merchant::query()
            ->with(['user', 'currency', 'supportedCurrencies'])
            ->where('status', MerchantStatus::REJECTED)
            ->filter($request)
            ->paginate(10)
            ->withQueryString();

        return view('backend.merchant.index', compact('merchants', 'title'));
    }

    public function merchantAction(MerchantActionRequest $request)
    {
        $validated = $request->validated();

        $merchant = Merchant::findOrFail($validated['merchant_id']);

        $action     = $validated['action'];
        $updateData = [];

        // Handle approval (includes fee update). If already approved, keep status; if disabled, only update fee.
        if ($action === 'approve') {
            // Update fee only on approve path
            $updateData['fee'] = $validated['fee'] ?? $merchant->fee;

            if ($merchant->status === MerchantStatus::PENDING) {
                $updateData['status'] = MerchantStatus::APPROVED;

                // Notify only on first-time approval from pending
                $merchant->user->notify(new TemplateNotification(
                    identifier: 'merchant_user_notify_shop_approved',
                    data: [
                        'business_name' => $merchant->business_name,
                    ],
                    action: route('user.merchant.index'),
                ));
            }
            // If current is APPROVED, we keep it approved; if DISABLED, we do not change status here
        }

        // Handle rejection only when not already rejected
        if ($action === 'reject' && $merchant->status === MerchantStatus::PENDING) {
            $updateData['status'] = MerchantStatus::REJECTED;
            $merchant->user->notify(new TemplateNotification(
                identifier: 'merchant_user_notify_shop_rejected',
                data: [
                    'business_name'    => $merchant->business_name,
                    'rejection_reason' => $validated['rejection_reason'] ?? __('No reason provided'),
                ],
                action: route('user.merchant.index'),
            ));
        }

        // Handle disable: only allowed from APPROVED
        if ($action === 'disable' && $merchant->status === MerchantStatus::APPROVED) {
            $updateData['status'] = MerchantStatus::DISABLED;
        }

        // Handle enable: only allowed from DISABLED
        if ($action === 'enable' && $merchant->status === MerchantStatus::DISABLED) {
            $updateData['status'] = MerchantStatus::APPROVED;
        }

        if (! empty($updateData)) {
            $merchant->update($updateData);
        }

        notifyEvs('success', 'Merchant  updated successfully');

        return back();
    }
}
