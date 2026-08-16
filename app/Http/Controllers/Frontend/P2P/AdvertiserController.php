<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend\P2P;

use App\Enums\P2P\OfferStatus;
use App\Enums\P2P\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\P2P\Offer;
use App\Models\P2P\OfferFeedback;
use App\Models\P2P\Order as P2POrder;
use App\Models\P2P\PaymentAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdvertiserController extends Controller
{
    public function show(User $user): View
    {
        $user->loadMissing(['kycSubmission']);

        $offers = Offer::query()
            ->with(['wallet.currency', 'paymentMethods', 'user.kycSubmission'])
            ->where('status', OfferStatus::ACTIVE)
            ->where('user_id', $user->id)
            ->orderBy('price', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalOrders = (int) P2POrder::query()
            ->where('maker_id', $user->id)
            ->count();

        $completedOrders = (int) P2POrder::query()
            ->where('maker_id', $user->id)
            ->where('status', OrderStatus::COMPLETED->value)
            ->count();

        $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0;

        $feedbackAgg = DB::table('p2p_offer_feedback as f')
            ->join('p2p_offers as o', 'o.id', '=', 'f.offer_id')
            ->where('o.user_id', $user->id)
            ->selectRaw('AVG(f.rating) as avg_rating')
            ->selectRaw('SUM(CASE WHEN f.rating >= 4 THEN 1 ELSE 0 END) as positive_feedback')
            ->selectRaw('COUNT(*) as feedback_count')
            ->first();

        $avgRating        = $feedbackAgg?->avg_rating !== null ? (float) $feedbackAgg->avg_rating : null;
        $positiveFeedback = (int) ($feedbackAgg->positive_feedback ?? 0);
        $feedbackCount    = (int) ($feedbackAgg->feedback_count ?? 0);

        $feedbacks = OfferFeedback::query()
            ->select('p2p_offer_feedback.*')
            ->join('p2p_offers', 'p2p_offers.id', '=', 'p2p_offer_feedback.offer_id')
            ->with('user')
            ->where('p2p_offers.user_id', $user->id)
            ->latest('p2p_offer_feedback.id')
            ->limit(10)
            ->get();

        $sellerStats = [
            (int) $user->id => [
                'total_orders'      => $totalOrders,
                'completed_orders'  => $completedOrders,
                'completion_rate'   => $completionRate,
                'avg_rating'        => $avgRating,
                'positive_feedback' => $positiveFeedback,
            ],
        ];

        $userPaymentAccounts = PaymentAccount::query()
            ->with('paymentMethod')
            ->where('user_id', auth()->id())
            ->latest('id')
            ->get();

        return view('frontend.user.p2p.traders.trader_profile', compact(
            'user',
            'offers',
            'sellerStats',
            'totalOrders',
            'completedOrders',
            'completionRate',
            'avgRating',
            'positiveFeedback',
            'feedbackCount',
            'feedbacks',
            'userPaymentAccounts'
        ));
    }
}
