<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\MerchantStatus;
use App\Enums\TrxStatus;
use App\Enums\TrxType;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use App\Support\UserDashboardKycNotice;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __construct(public UserDashboardKycNotice $kycNotice) {}

    /**
     * Display the dashboard view.
     */
    public function index(): View
    {
        $user = auth()->user();

        $relevantTypes  = $this->getRelevantTransactionTypes($user);
        $financialStats = $this->getFinancialStats($user, $relevantTypes);
        $staticStats    = $this->getStaticStats($user);
        $statistics     = array_merge($financialStats, $staticStats);

        // Define week day order to maintain consistent order in the view.
        $dayOrder          = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $weeklyStats       = $this->getWeeklyTransactionStats($user, $dayOrder);
        $sortedDeposits    = $weeklyStats['deposits'];
        $sortedWithdrawals = $weeklyStats['withdrawals'];

        // Calculate totals for the past 7 days.
        $totalSuccessDeposit  = $this->getTotalAmountForTrx($user, TrxType::DEPOSIT, 7);
        $totalSuccessWithdraw = $this->getTotalAmountForTrx($user, TrxType::WITHDRAW, 7);

        $transactions = $user->transactions()->latest()->take(3)->get();
        $user->loadMissing('kycSubmission');
        $kycNoticeCard = $this->kycNotice->forUser($user);

        return view('frontend.user.dashboard.index', compact(
            'statistics',
            'sortedDeposits',
            'sortedWithdrawals',
            'totalSuccessDeposit',
            'totalSuccessWithdraw',
            'transactions',
            'kycNoticeCard'
        ));
    }

    /**
     * Get the transaction types relevant for the user.
     */
    private function getRelevantTransactionTypes(User $user): array
    {
        $types = [
            TrxType::DEPOSIT,
            TrxType::WITHDRAW,
            TrxType::SEND_MONEY,
            TrxType::REQUEST_MONEY,
            TrxType::EXCHANGE_MONEY,
            TrxType::RECEIVE_MONEY,
            TrxType::REWARD,
        ];

        if ($user->isMerchant()) {
            $types[] = TrxType::RECEIVE_PAYMENT;
        }

        return $types;
    }

    /**
     * Retrieve financial statistics by grouping completed transactions.
     */
    private function getFinancialStats(User $user, array $relevantTypes): array
    {
        $transactions = $user->transactions()
            ->where('status', TrxStatus::COMPLETED)
            ->whereIn('trx_type', $relevantTypes)
            ->selectRaw('trx_type, currency, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('trx_type', 'currency')
            ->get();

        $stats         = [];
        $currencyRates = $this->currencyRatesFor($transactions);

        foreach ($relevantTypes as $trxType) {
            // Filter the transactions for the given type.
            $filtered = $transactions->where('trx_type', $trxType);

            // Format the total amount using a helper function.
            $formattedValue = $this->formatDefaultCurrencyAmount(
                $this->sumRowsInDefaultCurrency($filtered, 'total_amount', $currencyRates)
            );

            $stats[] = [
                'title'       => $trxType->label(),
                'value'       => $formattedValue,
                'icon'        => $trxType->icon(),
                'color_class' => $trxType->kebabCase(),
                'link'        => route('user.transaction.index'),
            ];
        }

        return $stats;
    }

    /**
     * Retrieve static statistics for the user.
     */
    private function getStaticStats(User $user): array
    {
        $stats = [
            [
                'title'       => __('Total Tickets'),
                'value'       => $user->tickets()->count(),
                'icon'        => 'tickets',
                'color_class' => 'tickets',
                'link'        => '#',
            ],
            [
                'title'       => __('Total Referrals'),
                'value'       => $user->referrals()->count(),
                'icon'        => 'referrals',
                'color_class' => 'referrals',
                'link'        => '#',
            ],
        ];

        if ($user->isMerchant()) {
            $stats[] = [
                'title'       => __('Merchant Shop'),
                'value'       => $user->merchants()->count(),
                'icon'        => 'merchant',
                'color_class' => 'merchant',
                'link'        => '#',
            ];

            $stats[] = [
                'title'       => __('Awaiting Merchant'),
                'value'       => $user->merchants()->where('status', MerchantStatus::PENDING)->count(),
                'icon'        => 'merchant-2',
                'color_class' => 'merchant-pending',
                'link'        => '#',
            ];
        }

        return $stats;
    }

    /**
     * Get weekly deposit and withdrawal statistics.
     */
    private function getWeeklyTransactionStats(User $user, array $dayOrder): array
    {
        $transactions = $user->transactions()
            ->whereIn('trx_type', [TrxType::DEPOSIT, TrxType::WITHDRAW])
            ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('
                DATE(created_at) as trx_date,
                trx_type,
                currency,
                SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as success_total,
                SUM(CASE WHEN status IN (?, ?) THEN amount ELSE 0 END) as fail_total
            ', [
                TrxStatus::COMPLETED->value,
                TrxStatus::FAILED->value,
                TrxStatus::CANCELED->value,
            ])
            ->groupBy('trx_date', 'trx_type', 'currency')
            ->get();

        $currencyRates = $this->currencyRatesFor($transactions);
        $symbol        = $this->defaultCurrencySymbol();

        // Map the deposits and withdrawals to the provided day order.
        $deposits = collect($dayOrder)->map(function (string $day) use ($transactions, $currencyRates, $symbol) {
            $data = $transactions
                ->where('trx_type', TrxType::DEPOSIT)
                ->filter(fn ($row): bool => Carbon::parse($row->trx_date)->format('D') === $day);

            return [
                'day'           => $day,
                'success_total' => round($this->sumRowsInDefaultCurrency($data, 'success_total', $currencyRates), 2),
                'fail_total'    => round($this->sumRowsInDefaultCurrency($data, 'fail_total', $currencyRates), 2),
                'symbol'        => $symbol,
            ];
        });

        $withdrawals = collect($dayOrder)->map(function (string $day) use ($transactions, $currencyRates, $symbol) {
            $data = $transactions
                ->where('trx_type', TrxType::WITHDRAW)
                ->filter(fn ($row): bool => Carbon::parse($row->trx_date)->format('D') === $day);

            return [
                'day'                    => $day,
                'withdraw_success_total' => round($this->sumRowsInDefaultCurrency($data, 'success_total', $currencyRates), 2),
                'withdraw_fail_total'    => round($this->sumRowsInDefaultCurrency($data, 'fail_total', $currencyRates), 2),
                'symbol'                 => $symbol,
            ];
        });

        return [
            'deposits'    => $deposits,
            'withdrawals' => $withdrawals,
        ];
    }

    /**
     * Get the total successful transaction amount for a given transaction type
     * over the past number of days.
     */
    private function getTotalAmountForTrx(User $user, TrxType $trxType, int $days): string
    {
        $totals = $user->transactions()
            ->where('trx_type', $trxType)
            ->where('status', TrxStatus::COMPLETED)
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('currency, SUM(amount) as total')
            ->groupBy('currency')
            ->get();

        return $this->formatDefaultCurrencyAmount(
            $this->sumRowsInDefaultCurrency($totals, 'total', $this->currencyRatesFor($totals))
        );
    }

    private function currencyRatesFor(Collection $rows): Collection
    {
        $codes = $rows
            ->pluck('currency')
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

    private function sumRowsInDefaultCurrency(Collection $rows, string $amountColumn, Collection $currencyRates): float
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

    private function convertToDefaultCurrency(float $amount, ?string $currency, string $defaultCurrency, Collection $currencyRates): float
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

    private function formatDefaultCurrencyAmount(float $amount): string
    {
        return $this->defaultCurrencySymbol().number_format($amount, (int) setting('site_decimal', 2));
    }

    private function defaultCurrencyCode(): string
    {
        return strtoupper((string) (siteCurrency() ?: config('app.default_currency', 'USD')));
    }

    private function defaultCurrencySymbol(): string
    {
        return (string) (siteCurrency('symbol') ?: config('app.default_currency_symbol', '$'));
    }
}
