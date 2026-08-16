<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\P2P;

use App\Enums\P2P\OrderStatus;
use App\Http\Controllers\Backend\BaseController;
use App\Models\P2P\Offer;
use App\Models\P2P\OfferFeedback;
use App\Models\P2P\Order as P2POrder;
use App\Models\User;
use App\Services\P2P\P2PTraderModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class P2PAdvertiserController extends BaseController
{
    // region Trader Directory and Profile Insights

    public static function permissions(): array
    {
        return [
            'index|show'         => 'p2p-manage',
            'suspend|reactivate' => 'p2p-manage',
        ];
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $advertiserIds = Offer::query()
            ->select('user_id')
            ->whereNotNull('user_id')
            ->distinct();

        $usersQuery = User::query()
            ->whereIn('id', $advertiserIds)
            ->with('kycSubmission')
            ->orderByDesc('id');

        if ($search !== '') {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->paginate(20)->withQueryString();

        $userIds = $users->getCollection()
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $stats = [];
        if ($userIds->isNotEmpty()) {
            $offerAgg = Offer::query()
                ->select('user_id')
                ->selectRaw('COUNT(*) as offers_count')
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $orderAgg = P2POrder::query()
                ->select('maker_id')
                ->selectRaw('COUNT(*) as total_orders')
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_orders', [OrderStatus::COMPLETED->value])
                ->whereIn('maker_id', $userIds)
                ->groupBy('maker_id')
                ->get()
                ->keyBy('maker_id');

            $feedbackAgg = DB::table('p2p_offer_feedback as f')
                ->join('p2p_offers as o', 'o.id', '=', 'f.offer_id')
                ->whereIn('o.user_id', $userIds)
                ->select('o.user_id')
                ->selectRaw('AVG(f.rating) as avg_rating')
                ->selectRaw('COUNT(*) as feedback_count')
                ->groupBy('o.user_id')
                ->get()
                ->keyBy('user_id');

            foreach ($userIds as $id) {
                $totalOrders     = (int) ($orderAgg[$id]->total_orders ?? 0);
                $completedOrders = (int) ($orderAgg[$id]->completed_orders ?? 0);
                $completionRate  = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0;

                $avgRating = isset($feedbackAgg[$id]) && $feedbackAgg[$id]->avg_rating !== null
                    ? (float) $feedbackAgg[$id]->avg_rating
                    : null;

                $stats[(int) $id] = [
                    'offers_count'     => (int) ($offerAgg[$id]->offers_count ?? 0),
                    'total_orders'     => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'completion_rate'  => $completionRate,
                    'avg_rating'       => $avgRating,
                    'feedback_count'   => (int) ($feedbackAgg[$id]->feedback_count ?? 0),
                ];
            }
        }

        return view('backend.p2p.traders.traders_list', compact('users', 'stats', 'search'));
    }

    public function show(User $user, Request $request): View
    {
        $user->loadMissing(['kycSubmission']);

        $offers = Offer::query()
            ->with(['wallet.currency', 'paymentMethods'])
            ->where('user_id', $user->id)
            ->latest('id')
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
            ->selectRaw('COUNT(*) as feedback_count')
            ->first();

        $avgRating     = $feedbackAgg?->avg_rating !== null ? (float) $feedbackAgg->avg_rating : null;
        $feedbackCount = (int) ($feedbackAgg->feedback_count ?? 0);

        $feedbacks = OfferFeedback::query()
            ->select('p2p_offer_feedback.*')
            ->join('p2p_offers', 'p2p_offers.id', '=', 'p2p_offer_feedback.offer_id')
            ->with('user')
            ->where('p2p_offers.user_id', $user->id)
            ->latest('p2p_offer_feedback.id')
            ->limit(20)
            ->get();

        return view('backend.p2p.traders.trader_details', compact(
            'user',
            'offers',
            'totalOrders',
            'completedOrders',
            'completionRate',
            'avgRating',
            'feedbackCount',
            'feedbacks'
        ));
    }

    // endregion

    // region Trader Moderation

    public function suspend(Request $request, User $user, P2PTraderModerationService $moderator): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($moderator->isSuspended($user)) {
            return back()->with('notifyevs', ['type' => 'info', 'message' => __('Trader is already suspended.')]);
        }

        $moderator->suspend($user, $validated['reason']);

        return back()->with('notifyevs', ['type' => 'success', 'message' => __('Trader suspended and open offers disabled.')]);
    }

    public function reactivate(User $user, P2PTraderModerationService $moderator): RedirectResponse
    {
        if (! $moderator->isSuspended($user)) {
            return back()->with('notifyevs', ['type' => 'info', 'message' => __('Trader is not currently suspended.')]);
        }

        $moderator->reactivate($user);

        return back()->with('notifyevs', ['type' => 'success', 'message' => __('Trader reactivated. Offers must be re-enabled by the trader.')]);
    }

    // endregion
}
