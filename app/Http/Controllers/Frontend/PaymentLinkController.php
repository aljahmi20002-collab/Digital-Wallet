<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\MerchantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentLink\StorePaymentLinkRequest;
use App\Http\Requests\PaymentLink\UpdatePaymentLinkRequest;
use App\Models\Merchant;
use App\Models\PaymentLink;
use App\Services\PaymentLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PaymentLinkController extends Controller
{
    public function __construct(protected PaymentLinkService $paymentLinks) {}

    public function index(): View
    {
        $paymentLinks = $this->paymentLinks->listForUser((int) auth()->id());

        return view('frontend.user.payment_links.index', compact('paymentLinks'));
    }

    public function create(Request $request): View
    {
        $currencies          = auth()->user()->activeWallets()->pluck('currency');
        $merchants           = $this->approvedMerchantsFor(auth()->user());
        $preselectMerchantId = $this->resolvePreselectedMerchantId($request, $merchants);

        return view('frontend.user.payment_links.create', compact('currencies', 'merchants', 'preselectMerchantId'));
    }

    public function store(StorePaymentLinkRequest $request): RedirectResponse
    {
        $this->paymentLinks->create($request->user(), $request->validated());

        notifyEvs('success', __('Payment link created successfully.'));

        return to_route('user.payment-links.index');
    }

    public function edit(PaymentLink $paymentLink): View|RedirectResponse
    {
        $this->authorizeOwnership($paymentLink);

        $currencies = auth()->user()->activeWallets()->pluck('currency');
        $merchants  = $this->approvedMerchantsFor(auth()->user());

        return view('frontend.user.payment_links.edit', [
            'paymentLink' => $paymentLink,
            'currencies'  => $currencies,
            'merchants'   => $merchants,
        ]);
    }

    public function update(UpdatePaymentLinkRequest $request, PaymentLink $paymentLink): RedirectResponse
    {
        $this->authorizeOwnership($paymentLink);

        $this->paymentLinks->update($paymentLink, $request->validated());

        notifyEvs('success', __('Payment link updated successfully.'));

        return to_route('user.payment-links.index');
    }

    public function toggle(PaymentLink $paymentLink): RedirectResponse
    {
        $this->authorizeOwnership($paymentLink);

        $this->paymentLinks->toggleStatus($paymentLink);

        notifyEvs('success', __('Payment link status updated.'));

        return back();
    }

    public function destroy(PaymentLink $paymentLink): RedirectResponse
    {
        $this->authorizeOwnership($paymentLink);

        $paymentLink->delete();

        notifyEvs('success', __('Payment link deleted.'));

        return to_route('user.payment-links.index');
    }

    /**
     * Enforce ownership for state-changing actions on a payment link.
     */
    protected function authorizeOwnership(PaymentLink $paymentLink): void
    {
        if ((int) $paymentLink->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    /**
     * Approved merchant shops the current user can attach to a payment
     * link. Returns an empty collection for non-merchant users so the
     * form gracefully degrades to general-link behaviour.
     */
    protected function approvedMerchantsFor($user): Collection
    {
        if (! $user || ! method_exists($user, 'can') || ! $user->can('merchant')) {
            return collect();
        }

        return Merchant::query()
            ->with(['currency', 'supportedCurrencies'])
            ->where('user_id', $user->id)
            ->where('status', MerchantStatus::APPROVED)
            ->orderBy('business_name')
            ->get();
    }

    /**
     * Resolve a `?merchant_id=` query param against the user's approved
     * merchant list. Returns null when the requested merchant isn't owned
     * by the user.
     */
    protected function resolvePreselectedMerchantId(Request $request, Collection $merchants): ?int
    {
        $requested = $request->integer('merchant_id');

        if ($requested <= 0) {
            return null;
        }

        return $merchants->firstWhere('id', $requested)?->id;
    }
}
