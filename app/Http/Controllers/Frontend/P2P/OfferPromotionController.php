<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend\P2P;

use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Models\P2P\Offer;
use App\Models\P2P\PromotionPackage;
use App\Models\Wallet;
use App\Services\P2P\P2POfferPromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OfferPromotionController extends Controller
{
    // region Trade Ad Promotion Flow

    public function __construct(protected P2POfferPromotionService $service) {}

    public function promote(Offer $offer): View
    {
        if ((int) $offer->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $offer->load(['promotion']);

        $offerSide = strtoupper(trim((string) ($offer->side?->value ?? $offer->side ?? '')));

        $packagesQuery = PromotionPackage::query()->where('status', true);
        if (Schema::hasColumn('p2p_promotion_packages', 'visibility')) {
            $packagesQuery->where('visibility', 'PUBLIC');
        }
        if ($offerSide !== '' && Schema::hasColumn('p2p_promotion_packages', 'applies_to')) {
            $packagesQuery->whereIn('applies_to', ['BOTH', $offerSide]);
        }

        $packages = $packagesQuery
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $wallets = Wallet::query()
            ->where('user_id', auth()->id())
            ->active()
            ->with('currency')
            ->orderBy('id')
            ->get();

        return view('frontend.user.p2p.trade_ads.promote_trade_ad', compact('offer', 'packages', 'wallets'));
    }

    public function quote(Request $request, Offer $offer): JsonResponse
    {
        if ((int) $offer->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'package_id' => 'required|integer|exists:p2p_promotion_packages,id',
            'wallet_id'  => 'required|integer|exists:wallets,id',
        ]);

        $package = PromotionPackage::query()
            ->where('id', $validated['package_id'])
            ->where('status', true)
            ->when(Schema::hasColumn('p2p_promotion_packages', 'visibility'), function ($q) {
                $q->where('visibility', 'PUBLIC');
            })
            ->firstOrFail();

        $appliesTo = strtoupper(trim((string) ($package->applies_to ?? 'BOTH')));
        $offerSide = strtoupper(trim((string) ($offer->side?->value ?? $offer->side ?? '')));
        if ($appliesTo !== 'BOTH' && $offerSide !== '' && $appliesTo !== $offerSide) {
            return response()->json([
                'success' => false,
                'message' => __('This promotion plan is not available for this trade ad.'),
            ], 422);
        }

        $wallet = Wallet::query()
            ->where('id', $validated['wallet_id'])
            ->where('user_id', auth()->id())
            ->where('status', true)
            ->with('currency')
            ->firstOrFail();

        try {
            $quote = $this->service->quote($package, $wallet);
        } catch (NotifyErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $promotion = $offer->promotion;

        return response()->json([
            'success' => true,
            'data'    => [
                'offer_id'         => (int) $offer->id,
                'package_id'       => (int) $package->id,
                'duration_minutes' => (int) $package->duration_minutes,
                'base_currency'    => $quote['base_currency'],
                'base_price'       => $quote['base_price'],
                'paid_currency'    => $quote['paid_currency'],
                'paid_amount'      => $quote['paid_amount'],
                'exchange_rate'    => $quote['exchange_rate'],
                'current_ends_at'  => $promotion?->ends_at?->toDateTimeString(),
                'current_status'   => $promotion?->status?->value,
            ],
        ]);
    }

    public function purchase(Request $request, Offer $offer): RedirectResponse
    {
        if ((int) $offer->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'package_id' => 'required|integer|exists:p2p_promotion_packages,id',
            'wallet_id'  => 'required|integer|exists:wallets,id',
        ]);

        $package = PromotionPackage::query()
            ->where('id', $validated['package_id'])
            ->where('status', true)
            ->when(Schema::hasColumn('p2p_promotion_packages', 'visibility'), function ($q) {
                $q->where('visibility', 'PUBLIC');
            })
            ->firstOrFail();

        $appliesTo = strtoupper(trim((string) ($package->applies_to ?? 'BOTH')));
        $offerSide = strtoupper(trim((string) ($offer->side?->value ?? $offer->side ?? '')));
        if ($appliesTo !== 'BOTH' && $offerSide !== '' && $appliesTo !== $offerSide) {
            notifyEvs('error', __('This promotion plan is not available for this trade ad.'));

            return back()->withInput();
        }

        $wallet = Wallet::query()
            ->where('id', $validated['wallet_id'])
            ->where('user_id', auth()->id())
            ->where('status', true)
            ->with('currency')
            ->firstOrFail();

        try {
            $this->service->purchase($offer, $package, $wallet, (int) auth()->id());
        } catch (NotifyErrorException $e) {
            notifyEvs('error', $e->getMessage());

            return back()->withInput();
        }

        notifyEvs('success', __('Your trade ad has been promoted successfully.'));

        return redirect()->route('user.p2p.offers.my');
    }

    // endregion
}
