<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserRank;
use Illuminate\View\View;

class UserRankController extends Controller
{
    public function showcase(): View
    {
        $userRanks = UserRank::query()
            ->where('is_active', true)
            ->orderBy('transaction_amount')
            ->get();

        return view('frontend.user.rank.showcase', compact('userRanks'));
    }
}
