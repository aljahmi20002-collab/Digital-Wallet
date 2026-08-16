<?php

namespace App\Http\Controllers\Backend;

use App\Enums\KycStatus;
use App\Enums\MerchantStatus;
use App\Enums\MethodType;
use App\Enums\TicketStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Enums\UserRole;
use App\Enums\VirtualCard\CardholderStatus;
use App\Enums\VirtualCard\VirtualCardRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Cardholders;
use App\Models\Currency;
use App\Models\KycSubmission;
use App\Models\Merchant;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCardRequest;
use App\Models\Wallet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {

        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : now()->subDays(14)->startOfDay();
        $end   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();

        $chartData = $this->generateChartData($start, $end);

        if ($request->ajax() && ($request->has('trx_chart') || $request->has('fee_chart') || $request->has('wallet_chart') || $request->has('end_date'))) {
            return match (true) {
                $request->has('trx_chart')    => response()->json($chartData),
                $request->has('fee_chart')    => response()->json($this->generateFeeChartData($start, $end)),
                $request->has('wallet_chart') => response()->json($this->generateWalletChartData($start, $end)),
                default                       => response()->json(['error' => 'Invalid chart type'], 400),
            };
        }

        $stats = $this->getAdminOverviewStats();

        $walletBalances = $this->walletBalances();

        $users        = User::latest()->limit(6)->get();
        $transactions = Transaction::latest()->limit(6)->get();

        // Build quick pending requests summary for top-of-dashboard shortcuts
        $quickRequests = [
            [
                'key'   => 'deposit',
                'title' => __('Deposits'),
                'count' => Transaction::query()
                    ->where('trx_type', TrxType::DEPOSIT)
                    ->where('status', TrxStatus::PENDING)
                    ->where('processing_type', MethodType::MANUAL)
                    ->count(),
                'icon'  => 'far fa-circle-down',
                'color' => 'req-deposit',
                'link'  => route('admin.deposit.manual-request'),
            ],
            [
                'key'   => 'withdraw',
                'title' => __('Withdrawals'),
                'count' => Transaction::query()
                    ->where('trx_type', TrxType::WITHDRAW)
                    ->where('status', TrxStatus::PENDING)
                    ->where('processing_type', MethodType::MANUAL)
                    ->count(),
                'icon'  => 'far fa-circle-up',
                'color' => 'req-withdraw',
                'link'  => route('admin.withdraw.manual-request'),
            ],
            [
                'key'   => 'kyc',
                'title' => __('KYC'),
                'count' => KycSubmission::query()->where('status', KycStatus::PENDING)->count(),
                'icon'  => 'far fa-address-card',
                'color' => 'req-kyc',
                'link'  => route('admin.kyc.pending'),
            ],
            [
                'key'   => 'merchant',
                'title' => __('Merchants'),
                'count' => Merchant::query()->where('status', MerchantStatus::PENDING)->count(),
                'icon'  => 'far fa-building',
                'color' => 'req-merchant',
                'link'  => route('admin.merchant.pending'),
            ],
            [
                'key'   => 'cardholder',
                'title' => __('Cardholders'),
                'count' => Cardholders::query()->where('status', CardholderStatus::PENDING)->count(),
                'icon'  => 'far fa-id-badge',
                'color' => 'req-cardholder',
                'link'  => route('admin.virtual-card.cardholders.index', ['status' => CardholderStatus::PENDING->value]),
            ],
            [
                'key'   => 'vc_request',
                'title' => __('Virtual Cards'),
                'count' => VirtualCardRequest::query()->where('status', VirtualCardRequestStatus::Pending)->count(),
                'icon'  => 'far fa-credit-card',
                'color' => 'req-vc',
                'link'  => route('admin.virtual-card.requests.awaiting'),
            ],
        ];

        return view('backend.dashboard.index', compact('stats', 'chartData', 'walletBalances', 'users', 'transactions', 'quickRequests'));
    }

    protected function getAdminOverviewStats(): Collection
    {
        $totalUsers            = User::query()->count();
        $activeUsers           = User::active()->count();
        $verifiedUsers         = User::query()->whereNotNull('email_verified_at')->count();
        $newUsersLastSevenDays = User::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $unverifiedUsers = User::query()->whereNull('email_verified_at')->count();
        $merchantUsers   = User::query()->where('role', UserRole::MERCHANT)->count();
        $storefronts     = Merchant::active()->count();
        $merchantQueue   = Merchant::query()->where('status', MerchantStatus::PENDING)->count();
        $kycQueue        = KycSubmission::query()->where('status', KycStatus::PENDING)->count();
        $ticketQueue     = Ticket::query()->where('status', TicketStatus::PENDING)->count();
        $depositQueue    = Transaction::query()
            ->where('trx_type', TrxType::DEPOSIT)
            ->where('status', TrxStatus::PENDING)
            ->where('processing_type', MethodType::MANUAL)
            ->count();
        $withdrawQueue = Transaction::query()
            ->where('trx_type', TrxType::WITHDRAW)
            ->where('status', TrxStatus::PENDING)
            ->where('processing_type', MethodType::MANUAL)
            ->count();
        $cardholderQueue  = Cardholders::query()->where('status', CardholderStatus::PENDING)->count();
        $cardRequestQueue = VirtualCardRequest::query()->where('status', VirtualCardRequestStatus::Pending)->count();
        $pendingActions   = $unverifiedUsers
            + $merchantQueue
            + $kycQueue
            + $ticketQueue
            + $depositQueue
            + $withdrawQueue
            + $cardholderQueue
            + $cardRequestQueue;

        return collect([
            [
                'key'         => 'total_users',
                'group'       => 'audience',
                'title'       => __('Total Users'),
                'value'       => $totalUsers,
                'meta'        => __('All accounts'),
                'icon'        => 'kpi-users',
                'color_class' => 'total',
                'link'        => route('admin.user.index'),
            ],
            [
                'key'         => 'active_users',
                'group'       => 'audience',
                'title'       => __('Active Users'),
                'value'       => $activeUsers,
                'meta'        => __('Can transact'),
                'icon'        => 'kpi-user-active',
                'color_class' => 'active-svg',
                'link'        => route('admin.user.active'),
            ],
            [
                'key'         => 'verified_users',
                'group'       => 'audience',
                'title'       => __('Verified Users'),
                'value'       => $verifiedUsers,
                'meta'        => __('Email verified'),
                'icon'        => 'kpi-shield-check',
                'color_class' => 'success-svg',
                'link'        => route('admin.user.index'),
            ],
            [
                'key'         => 'new_users',
                'group'       => 'audience',
                'title'       => __('New Users'),
                'value'       => $newUsersLastSevenDays,
                'meta'        => __('Last 7 days'),
                'icon'        => 'kpi-user-plus',
                'color_class' => 'success-svg',
                'link'        => route('admin.user.index'),
            ],
            [
                'key'         => 'pending_actions',
                'group'       => 'review',
                'title'       => __('Pending Actions'),
                'value'       => $pendingActions,
                'meta'        => __('Across review queues'),
                'icon'        => 'kpi-review-stack',
                'color_class' => 'warning-svg',
            ],
            [
                'key'         => 'unverified_users',
                'group'       => 'review',
                'title'       => __('Unverified'),
                'value'       => $unverifiedUsers,
                'meta'        => __('Email pending'),
                'icon'        => 'kpi-user-alert',
                'color_class' => 'danger-svg',
                'link'        => route('admin.user.unverified'),
            ],
            [
                'key'         => 'kyc_queue',
                'group'       => 'review',
                'title'       => __('KYC Queue'),
                'value'       => $kycQueue,
                'meta'        => __('Identity reviews'),
                'icon'        => 'kpi-kyc-review',
                'color_class' => 'info-svg',
                'link'        => route('admin.kyc.pending'),
            ],
            [
                'key'         => 'ticket_queue',
                'group'       => 'review',
                'title'       => __('Ticket Queue'),
                'value'       => $ticketQueue,
                'meta'        => __('Support backlog'),
                'icon'        => 'kpi-ticket',
                'color_class' => 'danger-svg',
                'link'        => route('admin.support-ticket.new'),
            ],
            [
                'key'         => 'merchants',
                'group'       => 'commerce',
                'title'       => __('Merchants'),
                'value'       => $merchantUsers,
                'meta'        => __('Merchant accounts'),
                'icon'        => 'kpi-merchant',
                'color_class' => 'info-svg',
            ],
            [
                'key'         => 'storefronts',
                'group'       => 'commerce',
                'title'       => __('Storefronts'),
                'value'       => $storefronts,
                'meta'        => __('Approved shops'),
                'icon'        => 'kpi-storefront',
                'color_class' => 'merchant',
                'link'        => route('admin.merchant.index', ['status' => MerchantStatus::APPROVED]),
            ],
            [
                'key'         => 'merchant_queue',
                'group'       => 'commerce',
                'title'       => __('Merchant Queue'),
                'value'       => $merchantQueue,
                'meta'        => __('Applications'),
                'icon'        => 'kpi-store-queue',
                'color_class' => 'info-svg',
                'link'        => route('admin.merchant.pending'),
            ],
            [
                'key'         => 'cardholder_queue',
                'group'       => 'commerce',
                'title'       => __('Cardholder Queue'),
                'value'       => $cardholderQueue,
                'meta'        => __('Profile reviews'),
                'icon'        => 'kpi-cardholder',
                'color_class' => 'merchant',
                'link'        => route('admin.virtual-card.cardholders.index', ['status' => CardholderStatus::PENDING->value]),
            ],
            [
                'key'         => 'deposit_queue',
                'group'       => 'money',
                'title'       => __('Deposit Queue'),
                'value'       => $depositQueue,
                'meta'        => __('Manual deposits'),
                'icon'        => 'kpi-deposit',
                'color_class' => 'deposit',
                'link'        => route('admin.deposit.manual-request'),
            ],
            [
                'key'         => 'withdraw_queue',
                'group'       => 'money',
                'title'       => __('Withdraw Queue'),
                'value'       => $withdrawQueue,
                'meta'        => __('Manual payouts'),
                'icon'        => 'kpi-withdraw',
                'color_class' => 'withdraw',
                'link'        => route('admin.withdraw.manual-request'),
            ],
            [
                'key'         => 'card_request_queue',
                'group'       => 'money',
                'title'       => __('Card Requests'),
                'value'       => $cardRequestQueue,
                'meta'        => __('Issuance queue'),
                'icon'        => 'kpi-card-request',
                'color_class' => 'payment',
                'link'        => route('admin.virtual-card.requests.awaiting'),
            ],
            $this->getTotalP2pFeeStat(),
        ])->values();
    }

    protected function generateChartData(Carbon $start, Carbon $end): array
    {
        $trxTypes = [
            'deposit'  => [TrxType::DEPOSIT],
            'withdraw' => [TrxType::WITHDRAW],
            'payment'  => [TrxType::PAYMENT],
            'reward'   => [TrxType::REWARD, TrxType::REFERRAL_REWARD],
        ];

        $transactions = Transaction::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', TrxStatus::COMPLETED)
            ->select('trx_type', 'amount', 'currency', 'created_at')
            ->get();

        $currencyRates = $this->currencyRatesFor($transactions);

        $grouped = [];

        foreach ($trxTypes as $key => $types) {
            $typeValues = array_map(fn ($t) => $t->value, $types);

            $grouped[$key] = collect($transactions)
                ->filter(fn ($trx) => in_array($trx->trx_type->value, $typeValues))
                ->groupBy(fn ($trx) => Carbon::parse($trx->created_at)->format('Y-m-d'))
                ->map(fn ($dailyTrxs) => $this->sumRowsInDefaultCurrency($dailyTrxs, 'amount', $currencyRates));
        }

        $dates = collect(CarbonPeriod::create($start, $end))
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        $formatted = collect($trxTypes)->mapWithKeys(function ($types, $key) use ($grouped, $dates) {
            $seriesData = collect($dates)->map(fn ($d) => round($grouped[$key][$d] ?? 0, 2));

            return [$key => [
                'name'  => ucfirst($key),
                'total' => $seriesData->sum(),
                'data'  => $seriesData,
            ]];
        });

        return [
            'dates'  => $dates,
            'series' => $formatted->values()->toArray(),
        ];
    }

    protected function walletBalances()
    {
        $currencies = Currency::withCount('wallets')
            ->with('wallets')
            ->whereHas('wallets')
            ->get();

        if ($currencies->isEmpty()) {
            return collect();
        }

        $defaultCurrency = Currency::getDefault();
        $defaultCode     = $defaultCurrency?->code ?? $this->defaultCurrencyCode();
        $currencyRates   = $this->currencyRatesFor($currencies, 'code');
        $totalCurrencies = $currencies->count();
        $activeCount     = $currencies->where('status', true)->count();

        return $currencies
            ->map(function (Currency $currency) use ($defaultCode, $currencyRates, $totalCurrencies, $activeCount): array {
                $nativeBalance      = (float) $currency->wallets->sum('balance');
                $convertedToDefault = $this->convertToDefaultCurrency(
                    $nativeBalance,
                    $currency->code,
                    $defaultCode,
                    $currencyRates
                );

                return [
                    'flag'                  => $currency->flag,
                    'icon_url'              => $currency->flag ? asset($currency->flag) : null,
                    'code'                  => $currency->code,
                    'name'                  => $currency->name,
                    'symbol'                => $currency->symbol,
                    'type'                  => $currency->type,
                    'total'                 => round($nativeBalance, 2),
                    'total_in_default'      => round($convertedToDefault, 2),
                    'count'                 => (int) $currency->wallets_count,
                    'status'                => (bool) $currency->status,
                    'is_default'            => $defaultCode && strcasecmp($currency->code, $defaultCode) === 0,
                    'rate_live'             => (bool) $currency->rate_live,
                    'exchange_rate'         => (float) ($currency->exchange_rate ?? 1),
                    'source_currency_count' => $totalCurrencies,
                    'active_currency_count' => $activeCount,
                    'converted_to_default'  => false,
                ];
            })
            ->sortByDesc(fn (array $row): float => (float) $row['total_in_default'])
            ->values();
    }

    protected function generateFeeChartData(Carbon $start, Carbon $end): array
    {
        $data = Transaction::selectRaw('DATE(created_at) as date, currency, SUM(fee) as total_fee')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', TrxStatus::COMPLETED)
            ->groupBy('date', 'currency')
            ->orderBy('date')
            ->get();

        $currencyRates = $this->currencyRatesFor($data);
        $feesByDate    = $data
            ->groupBy('date')
            ->map(fn (Collection $dailyFees): float => round(
                $this->sumRowsInDefaultCurrency($dailyFees, 'total_fee', $currencyRates),
                2
            ));

        $dates   = [];
        $amounts = [];

        foreach ($start->daysUntil($end) as $day) {
            $dateStr   = $day->toDateString();
            $dates[]   = $dateStr;
            $amounts[] = $feesByDate->get($dateStr, 0);
        }

        return [
            'series' => [
                [
                    'name' => __('Fee'),
                    'data' => $amounts,
                ],
            ],
            'categories' => $dates,
        ];
    }

    protected function generateWalletChartData(Carbon $start, Carbon $end): array
    {
        $wallets = Wallet::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', true)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dates  = [];
        $values = [];

        foreach ($start->daysUntil($end) as $day) {
            $dateStr  = $day->toDateString();
            $dates[]  = $dateStr;
            $values[] = $wallets[$dateStr]->total ?? 0;
        }

        return [
            'series' => [[
                'name' => __('New Wallets'),
                'data' => $values,
            ]],
            'categories' => $dates,
        ];
    }

    /**
     * P2P fee stats derived purely from transactions table.
     */
    protected function getTotalP2pFeeStat(): array
    {
        // Maker fee recorded at escrow time (minus refunds on cancel/expire)
        $makerFeeEscrows = Transaction::query()
            ->where('provider', 'p2p')
            ->where('trx_type', TrxType::P2P_ESCROW)
            ->where('status', TrxStatus::COMPLETED)
            ->selectRaw('currency, SUM(fee) as total_fee')
            ->groupBy('currency')
            ->get();

        $makerFeeRefunds = Transaction::query()
            ->where('provider', 'p2p')
            ->where('trx_type', TrxType::P2P_RELEASE)
            ->where('status', TrxStatus::COMPLETED)
            ->where(function ($q) {
                $q->where('trx_data->refund', true)
                    ->orWhere('trx_data->expired', true);
            })
            ->selectRaw("currency, SUM(JSON_EXTRACT(trx_data, '$.maker_fee_refund')) as total_fee")
            ->groupBy('currency')
            ->get();

        $takerFeeRefunds = Transaction::query()
            ->where('provider', 'p2p')
            ->where('trx_type', TrxType::P2P_RELEASE)
            ->where('status', TrxStatus::COMPLETED)
            ->where(function ($q) {
                $q->where('trx_data->refund', true)
                    ->orWhere('trx_data->expired', true);
            })
            ->selectRaw("currency, SUM(JSON_EXTRACT(trx_data, '$.taker_fee_refund')) as total_fee")
            ->groupBy('currency')
            ->get();

        // Taker fee recorded at release time (buyer credit)
        $takerFees = Transaction::query()
            ->where('provider', 'p2p')
            ->where('trx_type', TrxType::P2P_RELEASE)
            ->where('status', TrxStatus::COMPLETED)
            ->where('fee', '>', 0)
            ->selectRaw('currency, SUM(fee) as total_fee')
            ->groupBy('currency')
            ->get();

        $feeRows       = $makerFeeEscrows->merge($makerFeeRefunds)->merge($takerFeeRefunds)->merge($takerFees);
        $currencyRates = $this->currencyRatesFor($feeRows);

        $makerFeeEarned = round(
            $this->sumRowsInDefaultCurrency($makerFeeEscrows, 'total_fee', $currencyRates)
            - $this->sumRowsInDefaultCurrency($makerFeeRefunds, 'total_fee', $currencyRates),
            8
        );
        $takerFeeEarned = round(
            $this->sumRowsInDefaultCurrency($takerFees, 'total_fee', $currencyRates)
            - $this->sumRowsInDefaultCurrency($takerFeeRefunds, 'total_fee', $currencyRates),
            8
        );
        $totalFees = round($makerFeeEarned + $takerFeeEarned, 8);

        return [
            'key'         => 'p2p_fees',
            'group'       => 'money',
            'title'       => __('P2P Fees'),
            'value'       => $this->formatDefaultCurrencyAmount($totalFees),
            'meta'        => __('Default currency'),
            'icon'        => 'kpi-fees',
            'color_class' => 'info-svg',
        ];
    }

    protected function currencyRatesFor(Collection $rows, string $currencyColumn = 'currency'): Collection
    {
        $codes = $rows
            ->pluck($currencyColumn)
            ->filter()
            ->map(fn ($currency): string => strtoupper((string) $currency))
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            return collect();
        }

        return Currency::query()
            ->whereIn('code', $codes)
            ->get(['code', 'exchange_rate'])
            ->mapWithKeys(fn (Currency $currency): array => [
                strtoupper($currency->code) => (float) $currency->getRawOriginal('exchange_rate'),
            ]);
    }

    protected function sumRowsInDefaultCurrency(Collection $rows, string $amountColumn, Collection $currencyRates): float
    {
        $defaultCurrency = $this->defaultCurrencyCode();

        return (float) $rows->sum(function ($row) use ($amountColumn, $currencyRates, $defaultCurrency): float {
            return $this->convertToDefaultCurrency(
                (float) ($row->{$amountColumn} ?? 0),
                $row->currency ?? null,
                $defaultCurrency,
                $currencyRates
            );
        });
    }

    protected function convertToDefaultCurrency(float $amount, ?string $currency, string $defaultCurrency, Collection $currencyRates): float
    {
        $currency = strtoupper(trim((string) $currency));

        if ($currency === '' || $currency === $defaultCurrency) {
            return $amount;
        }

        $rate = (float) ($currencyRates->get($currency) ?? 0);

        if ($rate <= 0) {
            return $amount;
        }

        return $amount / $rate;
    }

    protected function formatDefaultCurrencyAmount(float $amount): string
    {
        return $this->defaultCurrencySymbol().number_format($amount, (int) setting('site_decimal', 2));
    }

    protected function defaultCurrencyCode(): string
    {
        return strtoupper((string) (siteCurrency('code') ?: config('app.default_currency', 'USD')));
    }

    protected function defaultCurrencySymbol(): string
    {
        return (string) (siteCurrency('symbol') ?: config('app.default_currency_symbol', '$'));
    }
}
