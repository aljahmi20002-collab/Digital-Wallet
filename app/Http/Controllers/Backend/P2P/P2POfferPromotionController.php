<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\P2P;

use App\Enums\P2P\PromotionStatus;
use App\Http\Controllers\Backend\BaseController;
use App\Models\P2P\OfferPromotion;
use App\Models\P2P\OfferPromotionPurchase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class P2POfferPromotionController extends BaseController
{
    // region Promotions (Active + Purchases tabs)

    private const TAB_ACTIVE = 'active';

    private const TAB_PURCHASES = 'purchases';

    public static function permissions(): array
    {
        return [
            'index|redirectLegacyPurchases' => 'p2p-manage',
        ];
    }

    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString();
        $tab = in_array($tab, [self::TAB_ACTIVE, self::TAB_PURCHASES], true) ? $tab : self::TAB_ACTIVE;

        $promotions = null;
        $purchases  = null;

        if ($tab === self::TAB_ACTIVE) {
            $promotions = $this->buildPromotionsQuery($request)->paginate(20)->withQueryString();
        } else {
            $purchases = $this->buildPurchasesQuery($request)->paginate(20)->withQueryString();
        }

        return view('backend.p2p.promotions.promotions', compact('tab', 'promotions', 'purchases'));
    }

    public function redirectLegacyPurchases(Request $request): RedirectResponse
    {
        return redirect()->route(
            'admin.p2p.promotions.index',
            ['tab' => self::TAB_PURCHASES] + $request->query()
        );
    }

    private function buildPromotionsQuery(Request $request): Builder
    {
        $query = OfferPromotion::query()
            ->with([
                'offer.wallet.currency',
                'user',
                'package',
                'wallet.currency',
            ])
            ->latest('id');

        if (($status = $request->string('status')->toString()) !== '') {
            $statusEnum = PromotionStatus::tryFrom(strtoupper($status));
            if ($statusEnum) {
                $query->where('status', $statusEnum);
            }
        }

        $this->applyCommonFilters($query, $request);

        return $query;
    }

    private function buildPurchasesQuery(Request $request): Builder
    {
        $query = OfferPromotionPurchase::query()
            ->with([
                'offer.wallet.currency',
                'user',
                'package',
                'wallet.currency',
            ])
            ->latest('id');

        $this->applyCommonFilters($query, $request);

        return $query;
    }

    private function applyCommonFilters(Builder $query, Request $request): void
    {
        if ($offerId = $request->integer('offer_id')) {
            $query->where('offer_id', $offerId);
        }

        if (($userSearch = trim($request->string('user_search')->toString())) !== '') {
            $query->whereHas('user', function (Builder $userQuery) use ($userSearch): void {
                $userQuery->where(function (Builder $nestedQuery) use ($userSearch): void {
                    $nestedQuery->where('username', 'like', '%'.$userSearch.'%')
                        ->orWhere('email', 'like', '%'.$userSearch.'%')
                        ->orWhere('first_name', 'like', '%'.$userSearch.'%')
                        ->orWhere('last_name', 'like', '%'.$userSearch.'%');
                });
            });
        }

        if ($packageId = $request->integer('package_id')) {
            $query->where('package_id', $packageId);
        }

        if (($trxId = $request->string('trx_id')->toString()) !== '') {
            $query->where('trx_id', 'like', '%'.$trxId.'%');
        }
    }

    // endregion
}
