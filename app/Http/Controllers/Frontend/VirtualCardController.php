<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\VirtualCard\CardholderStatus;
use App\Enums\VirtualCard\VirtualCardFeeOperation;
use App\Enums\VirtualCard\VirtualCardStatus;
use App\Exceptions\NotifyErrorException;
use App\Http\Controllers\Controller;
use App\Models\Cardholders;
use App\Models\Transaction;
use App\Models\VirtualCard;
use App\Models\VirtualCardFeeSetting;
use App\Models\VirtualCardProvider;
use App\Services\VirtualCard\VirtualCardManager;
use App\Services\VirtualCard\VirtualCardProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VirtualCardController extends Controller
{
    public function index()
    {
        $demoMode = config('app.demo') ?? false;

        $user = Auth::user();
        // Eager-load `request.cardholder.business` so the card visual can
        // render the actual cardholder's legal name (and business name for
        // company cards) — not the wallet currency code.
        $cards = $user->virtualCards()
            ->with(['wallet.currency', 'provider', 'request.cardholder.business'])
            ->latest()
            ->get();
        $wallets = $user->wallets()->with('currency')->get();

        $reqData = [
            'min_issue_fee'      => VirtualCardProvider::min('issue_fee'),
            'max_issue_fee'      => VirtualCardProvider::max('issue_fee'),
            'min_issue_fee_pct'  => VirtualCardProvider::min('issue_fee_pct'),
            'max_issue_fee_pct'  => VirtualCardProvider::max('issue_fee_pct'),
            'min_wallet_balance' => VirtualCardProvider::min('min_balance'),
            'max_wallet_balance' => VirtualCardProvider::max('min_balance'),
        ];

        $cardholders = Cardholders::where('user_id', $user->id)
            ->where('status', CardholderStatus::APPROVED)
            ->get();

        $stats       = $this->buildStats($user, $cards);
        $spendChart  = $this->buildSpendChart($user);
        $providerMix = $this->buildProviderMix($user);
        $networkList = $cards->pluck('network')
            ->filter()
            ->map(fn ($n) => is_object($n) ? ($n->value ?? (string) $n) : (string) $n)
            ->map(fn ($n) => Str::upper($n))
            ->unique()
            ->values()
            ->all();

        // Recent card-related transactions (top up + withdraw across all of the user's cards)
        $recentTxns = Transaction::query()
            ->where('user_id', $user->id)
            ->whereIn('trx_type', [TrxType::CARD_TOPUP, TrxType::CARD_WITHDRAW])
            ->latest()
            ->limit(10)
            ->get();

        return view('frontend.user.virtual_card.index', compact(
            'cards', 'wallets', 'reqData', 'demoMode', 'cardholders',
            'stats', 'spendChart', 'providerMix', 'networkList', 'recentTxns'
        ));
    }

    /**
     * Build the stats strip data: total balance, spent this month, pending auth,
     * total cards. Each headline number ships with an optional `trend` array
     * (`dir`, `pct`) when there is a previous-period number to compare against.
     */
    private function buildStats($user, $cards): array
    {
        $totalBalance = (float) $cards->sum(fn ($c) => (float) ($c->wallet->balance ?? 0));

        $thisMonthSpend = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('trx_type', TrxType::CARD_TOPUP)
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('amount');

        $lastMonthSpend = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('trx_type', TrxType::CARD_TOPUP)
            ->whereBetween('created_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->sum('amount');

        $pendingQuery = Transaction::query()
            ->where('user_id', $user->id)
            ->whereIn('trx_type', [TrxType::CARD_TOPUP, TrxType::CARD_WITHDRAW])
            ->where('status', TrxStatus::PENDING);

        $pendingAuth      = (float) (clone $pendingQuery)->sum('amount');
        $pendingAuthCount = (int) (clone $pendingQuery)->count();

        $activeCount = $cards->where('status', VirtualCardStatus::Active)->count();
        $frozenCount = $cards->whereIn('status', [VirtualCardStatus::Inactive, VirtualCardStatus::Blocked])->count();

        return [
            'total_balance'   => $totalBalance,
            'monthly_spend'   => $thisMonthSpend,
            'monthly_trend'   => $this->trend($thisMonthSpend, $lastMonthSpend),
            'pending_auth'    => $pendingAuth,
            'pending_count'   => $pendingAuthCount,
            'total_cards'     => $cards->count(),
            'active_cards'    => $activeCount,
            'frozen_cards'    => $frozenCount,
            'providers_count' => $cards->pluck('provider_id')->unique()->count(),
        ];
    }

    private function trend(float $current, float $previous): ?array
    {
        if ($previous <= 0) {
            return null;
        }
        $delta = (($current - $previous) / $previous) * 100;

        return [
            'dir' => $delta >= 0 ? 'up' : 'down',
            'pct' => round(abs($delta), 1),
        ];
    }

    /**
     * Daily CARD_TOPUP totals for the last 90 days. The view exposes 7d/30d/90d
     * windows over the same dataset (no extra round trips).
     */
    private function buildSpendChart($user): array
    {
        $rows = Transaction::query()
            ->where('user_id', $user->id)
            ->where('trx_type', TrxType::CARD_TOPUP)
            ->where('status', TrxStatus::COMPLETED)
            ->whereBetween('created_at', [now()->subDays(89)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->mapWithKeys(fn ($v, $k) => [(string) $k => (float) $v])
            ->all();

        $series = [];
        for ($i = 89; $i >= 0; $i--) {
            $day      = now()->subDays($i)->toDateString();
            $series[] = [
                'date'  => $day,
                'label' => now()->subDays($i)->format('M d'),
                'value' => (float) ($rows[$day] ?? 0),
            ];
        }

        return [
            'series' => $series,
            'totals' => [
                '7d'  => array_sum(array_column(array_slice($series, -7), 'value')),
                '30d' => array_sum(array_column(array_slice($series, -30), 'value')),
                '90d' => array_sum(array_column($series, 'value')),
            ],
        ];
    }

    /**
     * "By provider" donut data — month-to-date CARD_TOPUP grouped by provider name
     * (already populated on every Transaction by VirtualCardManager).
     */
    private function buildProviderMix($user): array
    {
        $rows = Transaction::query()
            ->where('user_id', $user->id)
            ->where('trx_type', TrxType::CARD_TOPUP)
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->selectRaw('provider, SUM(amount) as total')
            ->groupBy('provider')
            ->pluck('total', 'provider')
            ->all();

        $palette = ['#3B6FE0', '#7C5CFF', '#10B981', '#F59E0B', '#9AA3B2', '#EF4444'];
        $total   = (float) array_sum($rows);

        $items = [];
        $i     = 0;
        foreach ($rows as $name => $value) {
            $value   = (float) $value;
            $items[] = [
                'name'  => $name ?: __('Unknown'),
                'value' => $value,
                'pct'   => $total > 0 ? round(($value / $total) * 100, 1) : 0,
                'color' => $palette[$i % count($palette)],
            ];
            $i++;
        }

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Provider-agnostic card details endpoint.
     * Delegates to the resolved provider's getCardDetails() and returns JSON.
     */
    public function cardDetails(Request $request, VirtualCardProviderFactory $factory, $id, ?string $providerName = null)
    {
        // Force JSON for the entire response cycle — including any
        // middleware redirect (e.g. unauthenticated users would otherwise
        // get an HTML login page back, which the front-end JS then can't
        // parse, surfacing as "Unexpected token '<'" in the console).
        $request->headers->set('Accept', 'application/json');

        if (! Auth::check()) {
            return response()->json(['error' => __('Unauthenticated.')], 401);
        }

        $card = VirtualCard::with('provider')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $card) {
            return response()->json(['error' => __('Card not found.')], 404);
        }

        $providerCode = $card->provider->code ?? $providerName;

        if (! $providerCode) {
            return response()->json(['error' => __('No virtual card provider is linked to this card.')], 422);
        }

        try {
            $provider = $factory->getProvider($providerCode);
            $payload  = $provider->getCardDetails($card);

            // Normalize: providers may return either an array or pre-rendered HTML.
            if (is_string($payload)) {
                return response()->json(['html' => $payload]);
            }

            return response()->json(['data' => $payload]);
        } catch (\Throwable $e) {
            Log::error('Virtual card details fetch failed', [
                'card_id'  => $card->id,
                'provider' => $providerCode,
                'error'    => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function topup($card)
    {
        $card         = VirtualCard::find($card);
        $cardSettings = VirtualCardFeeSetting::where('provider_id', $card->provider_id)
            ->where('currency_id', $card->wallet->currency_id)
            ->where('operation', VirtualCardFeeOperation::Topup->value)
            ->first();

        return view('frontend.user.virtual_card.topup.index', compact('card', 'cardSettings'));
    }

    public function withdraw($card)
    {
        $card         = VirtualCard::find($card);
        $cardSettings = VirtualCardFeeSetting::where('provider_id', $card->provider_id)
            ->where('currency_id', $card->wallet->currency_id)
            ->where('operation', VirtualCardFeeOperation::Withdrawal->value)
            ->first();

        return view('frontend.user.virtual_card.withdraw.index', compact('card', 'cardSettings'));
    }

    public function topupStore(Request $request, VirtualCardManager $cardManager)
    {
        $validated = $request->validate([
            'card_id' => ['required', 'integer', 'exists:virtual_cards,id'],
            'amount'  => ['required', 'numeric', 'gt:0'],
        ]);

        DB::beginTransaction();
        try {
            $cardManager->topup($validated['card_id'], $validated['amount']);
            DB::commit();

            notifyEvs('success', __('Top-up successful.'));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true]);
            }

            return redirect()->route('user.virtual-card.topup', ['card' => $validated['card_id']]);
        } catch (NotifyErrorException $e) {
            DB::rollBack();
            Log::warning('Virtual Card Top-up Validation Error', [
                'user_id' => auth()->id(),
                'card_id' => $validated['card_id'],
                'error'   => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Virtual Card Top-up Error', [
                'user_id' => auth()->id(),
                'card_id' => $validated['card_id'],
                'error'   => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => __('Something went wrong. Please try again.')], 500);
            }

            return back()->withErrors(['amount' => __('Something went wrong. Please try again.')])->withInput();
        }
    }

    public function withdrawStore(Request $request, VirtualCardManager $cardManager)
    {
        $validated = $request->validate([
            'card_id' => ['required', 'integer', 'exists:virtual_cards,id'],
            'amount'  => ['required', 'numeric', 'gt:0'],
        ]);

        DB::beginTransaction();
        try {
            $cardManager->withdraw($validated['card_id'], $validated['amount']);
            DB::commit();

            notifyEvs('success', __('Withdraw successful.'));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true]);
            }

            return redirect()->route('user.virtual-card.withdraw', ['card' => $validated['card_id']]);
        } catch (NotifyErrorException $e) {
            DB::rollBack();
            Log::warning('Virtual Card Withdraw Validation Error', [
                'user_id' => auth()->id(),
                'card_id' => $validated['card_id'],
                'error'   => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Virtual Card Withdraw Error', [
                'user_id' => auth()->id(),
                'card_id' => $validated['card_id'],
                'error'   => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => __('Something went wrong. Please try again.')], 500);
            }

            return back()->withErrors(['amount' => __('Something went wrong. Please try again.')])->withInput();
        }
    }

    /**
     * Freeze (block) a virtual card. Provider-agnostic — falls back to a soft DB
     * status flip when the provider's gateway has no freeze API.
     */
    public function freeze(Request $request, VirtualCard $card, VirtualCardProviderFactory $factory)
    {
        $this->authorizeCard($card);

        $payload = $request->validate([
            'reason'   => ['nullable', 'in:lost,suspicious,pause'],
            'duration' => ['nullable', 'in:1h,24h,7d,indefinite'],
        ]);

        try {
            $provider = $factory->getProvider($card->provider->code);
            $result   = $provider->freezeCard($card);

            $meta           = $card->meta ?? [];
            $meta['freeze'] = array_filter([
                'reason'    => $payload['reason']   ?? null,
                'duration'  => $payload['duration'] ?? 'indefinite',
                'frozen_at' => now()->toIso8601String(),
                'soft'      => (bool) ($result['soft'] ?? false),
            ], fn ($v) => $v !== null && $v !== '');

            $card->update([
                'status' => VirtualCardStatus::Inactive->value,
                'meta'   => $meta,
            ]);

            return response()->json([
                'ok'      => true,
                'soft'    => $result['soft'] ?? false,
                'status'  => VirtualCardStatus::Inactive->value,
                'message' => __('Card frozen successfully.'),
            ]);
        } catch (NotifyErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Virtual card freeze failed', ['error' => $e->getMessage(), 'card_id' => $card->id]);

            return response()->json(['error' => __('Failed to freeze card.')], 500);
        }
    }

    public function unfreeze(VirtualCard $card, VirtualCardProviderFactory $factory)
    {
        $this->authorizeCard($card);

        try {
            $provider = $factory->getProvider($card->provider->code);
            $result   = $provider->unfreezeCard($card);

            $card->update(['status' => VirtualCardStatus::Active->value]);

            return response()->json([
                'ok'      => true,
                'soft'    => $result['soft'] ?? false,
                'status'  => VirtualCardStatus::Active->value,
                'message' => __('Card unfrozen successfully.'),
            ]);
        } catch (NotifyErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Virtual card unfreeze failed', ['error' => $e->getMessage(), 'card_id' => $card->id]);

            return response()->json(['error' => __('Failed to unfreeze card.')], 500);
        }
    }

    /**
     * Save spend limits to the card's `meta` blob. Limits are advisory by default;
     * a provider that wires `setLimits()` into its API can extend this later.
     */
    public function updateLimits(Request $request, VirtualCard $card)
    {
        $this->authorizeCard($card);

        $data = $request->validate([
            'per_transaction' => ['nullable', 'numeric', 'gte:0'],
            'daily'           => ['nullable', 'numeric', 'gte:0'],
            'monthly'         => ['nullable', 'numeric', 'gte:0'],
            'alert_at'        => ['nullable', 'integer', 'between:0,100'],
            'auto_freeze'     => ['nullable', 'boolean'],
        ]);

        $meta           = $card->meta ?? [];
        $meta['limits'] = array_filter([
            'per_transaction' => $data['per_transaction'] ?? null,
            'daily'           => $data['daily']           ?? null,
            'monthly'         => $data['monthly']         ?? null,
            'alert_at'        => $data['alert_at']        ?? null,
            'auto_freeze'     => $request->boolean('auto_freeze'),
        ], fn ($v) => $v !== null);

        $card->update(['meta' => $meta]);

        return response()->json(['ok' => true, 'message' => __('Limits saved.')]);
    }

    /**
     * Save card controls (online/atm/intl/contactless) to the card's `meta` blob.
     */
    public function updateControls(Request $request, VirtualCard $card)
    {
        $this->authorizeCard($card);

        $data = $request->validate([
            'online'      => ['nullable', 'boolean'],
            'atm'         => ['nullable', 'boolean'],
            'intl'        => ['nullable', 'boolean'],
            'contactless' => ['nullable', 'boolean'],
        ]);

        $meta             = $card->meta ?? [];
        $meta['controls'] = [
            'online'      => $request->boolean('online'),
            'atm'         => $request->boolean('atm'),
            'intl'        => $request->boolean('intl'),
            'contactless' => $request->boolean('contactless'),
        ];

        $card->update(['meta' => $meta]);

        return response()->json(['ok' => true, 'message' => __('Controls updated.')]);
    }

    private function authorizeCard(VirtualCard $card): void
    {
        if ($card->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
