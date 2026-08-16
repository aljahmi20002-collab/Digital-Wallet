<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\EnvironmentMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\StoreMerchantRequest;
use App\Http\Requests\Merchant\SwitchMerchantEnvironmentRequest;
use App\Http\Requests\Merchant\UpdateMerchantPaymentMethodsRequest;
use App\Http\Requests\Merchant\UpdateMerchantRequest;
use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class MerchantController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected MerchantService $merchants) {}

    public function index(): View
    {
        $merchants = Merchant::query()
            ->with(['currency', 'supportedCurrencies', 'paymentMethods.paymentGateway'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $walletCurrencyCodes = auth()->user()
            ->wallets()
            ->active()
            ->with('currency')
            ->get()
            ->pluck('currency.code')
            ->map(fn ($code): string => strtoupper((string) $code))
            ->filter()
            ->unique()
            ->values();

        return view('frontend.user.merchant.index', compact('merchants', 'walletCurrencyCodes'));
    }

    public function create()
    {
        $currencies = auth()->user()->activeWallets()->pluck('currency');

        return view('frontend.user.merchant.create', compact('currencies'));
    }

    public function store(StoreMerchantRequest $request)
    {
        $this->merchants->createForUser(
            $request->user(),
            $request->validated(),
            $request->file('business_logo')
        );

        notifyEvs('success', __('New Merchant Request Successfully'));

        return to_route('user.merchant.index');
    }

    public function edit(Merchant $merchant)
    {
        $this->authorize('update', $merchant);

        if ($merchant->isActionLocked()) {
            notifyEvs('error', __('This merchant is disabled or rejected. Editing is not allowed.'));

            return to_route('user.merchant.index');
        }

        $currencies = auth()->user()->activeWallets()->pluck('currency');

        return view('frontend.user.merchant.edit', compact('merchant', 'currencies'));
    }

    public function update(UpdateMerchantRequest $request, Merchant $merchant)
    {
        $this->authorize('update', $merchant);

        if ($merchant->isActionLocked()) {
            notifyEvs('error', __('This merchant is disabled or rejected. Updating is not allowed.'));

            return to_route('user.merchant.index');
        }

        $this->merchants->updateMerchant(
            $merchant,
            $request->validated(),
            $request->file('business_logo')
        );

        notifyEvs('success', __('Merchant details updated successfully'));

        return to_route('user.merchant.index');
    }

    public function merchantConfig(Merchant $merchant)
    {
        $this->authorize('view', $merchant);

        if ($merchant->isActionLocked()) {
            notifyEvs('error', __('This merchant is disabled or rejected. Config is not available.'));

            return to_route('user.merchant.index');
        }

        $merchant->loadMissing(['currency', 'supportedCurrencies', 'paymentMethods.paymentGateway', 'user']);

        $paymentMethods           = $this->merchants->eligiblePaymentMethods($merchant);
        $eligiblePaymentMethodIds = $paymentMethods
            ->pluck('id')
            ->map(fn ($id): int => (int) $id);
        $selectedPaymentMethodIds = $merchant->paymentMethods
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->intersect($eligiblePaymentMethodIds)
            ->values()
            ->all();

        return view('frontend.user.merchant.config', compact('merchant', 'paymentMethods', 'selectedPaymentMethodIds'));
    }

    public function updatePaymentMethods(UpdateMerchantPaymentMethodsRequest $request, Merchant $merchant): RedirectResponse
    {
        $this->authorize('update', $merchant);

        if ($merchant->isActionLocked()) {
            notifyEvs('error', __('This merchant is disabled or rejected. Gateway settings are not available.'));

            return to_route('user.merchant.index');
        }

        $this->merchants->syncPaymentMethods($merchant, (array) $request->input('payment_method_ids', []));

        notifyEvs('success', __('Merchant payment gateways updated successfully.'));

        return back();
    }

    /**
     * Switch merchant environment between sandbox and production
     */
    public function switchEnvironment(SwitchMerchantEnvironmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $merchant  = Merchant::findOrFail($validated['merchant_id']);
        $this->authorize('update', $merchant);

        if ($merchant->isActionLocked()) {
            return response()->json([
                'success' => false,
                'message' => __('This merchant is disabled or rejected.'),
            ], 403);
        }

        try {
            $environmentEnum = EnvironmentMode::from($validated['environment']);

            if ($environmentEnum->isProduction() && ! $merchant->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Production mode is available after merchant approval.'),
                ], 403);
            }

            // Generate test credentials if switching to sandbox and they don't exist
            if ($environmentEnum->isSandbox() && ! $merchant->hasTestCredentials()) {
                $merchant->generateTestCredentials();
            }

            // Switch environment using enum
            if ($environmentEnum->isSandbox()) {
                $success = $merchant->switchToSandbox();
            } else {
                $success = $merchant->switchToProduction();
            }

            if ($success) {
                $merchant->refresh();

                $message = $environmentEnum->isSandbox()
                    ? __('Successfully switched to sandbox mode. You can now test your API integration safely.')
                    : __('Successfully switched to production mode. Your API will now process live transactions.');

                return response()->json([
                    'success'      => true,
                    'message'      => $message,
                    'current_mode' => $merchant->current_mode->value,
                    'credentials'  => $this->environmentCredentials($merchant, $environmentEnum),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to switch environment. Please try again.'),
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Environment switch failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while switching environment.'),
            ], 500);
        }
    }

    /**
     * @return array<string, string>
     */
    private function environmentCredentials(Merchant $merchant, EnvironmentMode $environment): array
    {
        if ($environment->isSandbox()) {
            return [
                'merchant_key' => $merchant->test_merchant_key ?: __('Generate by switching to sandbox'),
                'api_key'      => $merchant->test_api_key ?: __('Generate by switching to sandbox'),
                'api_secret'   => $merchant->test_api_secret ?: __('Generate by switching to sandbox'),
            ];
        }

        return [
            'merchant_key' => $merchant->getRawOriginal('merchant_key') ?: __('Available after approval'),
            'api_key'      => $merchant->getRawOriginal('api_key') ?: __('No API Key Generated'),
            'api_secret'   => $merchant->getRawOriginal('api_secret') ?: __('No API Secret Generated'),
        ];
    }
}
