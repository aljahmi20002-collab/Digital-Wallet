@php
    use App\Enums\AmountFlow;
    use App\Enums\TrxStatus;
    use App\Enums\TrxType;

    $mobileTransactions = $transactions->getCollection();
    $mobileFiltersOpen = request()->filled('type')
        || request()->filled('status')
        || request()->filled('search')
        || request()->filled('daterange');
    $mobileActiveFilterCount = collect([
        request('type'),
        request('status'),
        request('search'),
        request('daterange'),
    ])->filter(fn ($value): bool => filled($value))->count();
    $mobileCompletedCount = $mobileTransactions
        ->filter(fn ($transaction): bool => $transaction->status === TrxStatus::COMPLETED)
        ->count();
    $mobilePendingCount = $mobileTransactions
        ->filter(fn ($transaction): bool => $transaction->status === TrxStatus::PENDING)
        ->count();
    $mobileGroups = $mobileTransactions->groupBy(function ($transaction): string {
        $date = $transaction->created_at->copy()->startOfDay();

        if ($date->equalTo(today())) {
            return __('Today');
        }

        if ($date->equalTo(today()->subDay())) {
            return __('Yesterday');
        }

        return $transaction->created_at->format('d M Y');
    });
@endphp

@extends('frontend.layouts.user.index')
@section('title', __('Transactions'))
@section('content')
    <div class="user-dashboard user-transaction-page d-none d-lg-block">
        <div class="row">
            <div class="col-12">
                <div class="card single-form-card user-transaction-page__shell">
                    {{-- Card Header --}}
                    <x-user-feature-header
                        :title="__('Transactions')"
                        :subtitle="__('Search, filter, and review all wallet movements.')"
                        icon="fas fa-receipt"
                    >
                        <div class="d-md-none">
                            <button class="btn btn-light-primary d-flex align-items-center" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#filterSection" aria-expanded="false"
                                    aria-controls="filterSection">
                                <i class="fa-solid fa-filter"></i> {{ __('Filters') }}
                            </button>
                        </div>
                    </x-user-feature-header>

                    {{-- Card Body --}}
                    <div class="card-body">
                        {{-- Filter Section --}}
                        <div class="collapse d-md-block" id="filterSection">
                            <div class="ud-filter-panel card card-body">
                                <form action="{{ route('user.transaction.index') }}" method="GET"
                                      class="row gy-3 align-items-end">
                                    {{-- Date Range --}}
                                    <div class="col-md-auto">
                                        <label for="reportrange" class="form-label small">{{ __('Date Range') }}</label>
                                        <div class="input-group">
                                            <input type="hidden" name="daterange" value="">
                                            <div id="reportrange" class="form-control rounded d-flex align-items-center"
                                                 role="button" tabindex="0" aria-label="{{ __('Select date range') }}">
                                                <i class="fa-solid fa-calendar-days me-2"></i>
                                                <span class="flex-grow-1"></span>
                                                <i class="fa-solid fa-angle-down ms-2"></i>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Transaction Type --}}
                                    <div class="col-md-auto">
                                        <x-form.select name="type" label="{{ __('Transaction Type') }}"
                                                       :options="TrxType::options()"
                                                       :selected="request('type')"/>
                                    </div>

                                    {{-- Transaction Status --}}
                                    <div class="col-md-auto">
                                        <x-form.select name="status" label="{{ __('Transaction Status') }}"
                                                       :options="TrxStatus::options()"
                                                       :selected="request('status')"/>
                                    </div>

                                    {{-- Search --}}
                                    <div class="col-md">
                                        <label for="search" class="form-label small">{{ __('Search') }}</label>
                                        <div class="input-group">
                                            <input
                                                    type="text"
                                                    name="search"
                                                    id="search"
                                                    value="{{ request('search') }}"
                                                    class="form-control"
                                                    placeholder="{{ __('Search...') }}"
                                                    aria-label="{{ __('Search') }}"
                                            >
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Transactions List --}}
                        <section class="ud-transactions ud-transactions--page mt-4">
                            <header class="ud-transactions__head">
                                <div class="ud-transactions__intro">
                                    <h3 class="ud-transactions__title">@lang('All Transactions')</h3>
                                    <p class="ud-transactions__subtitle">@lang('Recent wallet activity, statuses, and transaction references in one stream.')</p>
                                </div>
                                <span class="ud-transactions__summary">{{ $transactions->total() }} {{ __('records') }}</span>
                            </header>

                            <div class="ud-transactions__list">
                                @forelse($transactions as $transaction)
                                    @php
                                        $transactionTypeClass = $transaction->trx_type->kebabCase();
                                        $icon = $transaction->trx_type->icon();
                                        $amountColor = $transaction->amount_flow->color($transaction->status);
                                        $amountSign = $transaction->amount_flow->sign($transaction->status);
                                    @endphp

                                    <div class="ud-trx-item" role="button" data-bs-toggle="modal"
                                         data-bs-target="#transactionModal{{ $transaction->id }}">
                                        <span class="ud-trx-item__icon {{ $transactionTypeClass }}">
                                            <x-icon name="{{ $icon }}" height="20" width="20"/>
                                        </span>

                                        <div class="ud-trx-item__body">
                                            <div class="ud-trx-item__primary">
                                                <span class="ud-trx-item__title">{{ $transaction->description }}</span>
                                                <span class="ud-trx-item__amount {{ $amountColor }}">
                                                    {{ $amountSign.number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                                                </span>
                                            </div>
                                            <div class="ud-trx-item__secondary">
                                                <div class="ud-trx-item__tags">
                                                    <span class="ud-trx-chip ud-trx-chip--type {{ $transactionTypeClass }}">
                                                        {{ title($transaction->trx_type->value) }}
                                                    </span>
                                                    <span class="ud-trx-chip ud-trx-chip--status bg-{{ $transaction->status->color() }}">
                                                        {{ strtoupper($transaction->status->value) }}
                                                    </span>
                                                </div>
                                                <div class="ud-trx-item__meta">
                                                    <span>{{ __('TRX') }}: {{ strtoupper($transaction->trx_id) }}</span>
                                                    <span>{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <x-user-not-found
                                        :title="__('No transactions found')"
                                        :message="__('Try adjusting the filters or search terms to find matching wallet activity.')"
                                        :eyebrow="__('Transaction stream')"
                                        icon="fa-receipt"
                                    />
                                @endforelse
                            </div>
                        </section>

                        {{-- Pagination --}}
                        @if($transactions->hasPages())
                            <div class="ud-transactions__pagination mt-3 d-flex justify-content-center">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="dk-history-page d-lg-none">
        <div class="dk-history-hero">
            <div class="dk-history-hero__top">
                <div class="dk-history-hero__copy">
                    <span class="dk-history-hero__eyebrow">{{ __('Wallet ledger') }}</span>
                    <h1>{{ __('History') }}</h1>
                    <p>{{ __('Every wallet movement, status, and receipt in one mobile stream.') }}</p>
                </div>
                <span class="dk-history-hero__icon" aria-hidden="true">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
            </div>

            <div class="dk-history-stats" aria-label="{{ __('Transaction summary') }}">
                <div class="dk-history-stat">
                    <span>{{ __('Records') }}</span>
                    <strong>{{ number_format($transactions->total()) }}</strong>
                </div>
                <div class="dk-history-stat">
                    <span>{{ __('Completed') }}</span>
                    <strong>{{ number_format($mobileCompletedCount) }}</strong>
                </div>
                <div class="dk-history-stat">
                    <span>{{ __('Pending') }}</span>
                    <strong>{{ number_format($mobilePendingCount) }}</strong>
                </div>
            </div>
        </div>

        <details class="dk-history-filter" {{ $mobileFiltersOpen ? 'open' : '' }}>
            <summary>
                <span>
                    <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                    {{ __('Filters') }}
                </span>
                @if($mobileActiveFilterCount > 0)
                    <b>{{ $mobileActiveFilterCount }}</b>
                @endif
                <i class="fa-solid fa-angle-down" aria-hidden="true"></i>
            </summary>

            <form action="{{ route('user.transaction.index') }}" method="GET" class="dk-history-filter__form">
                <label class="dk-history-field">
                    <span>{{ __('Search') }}</span>
                    <input type="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('TRX ID, type, or note') }}">
                </label>

                <label class="dk-history-field">
                    <span>{{ __('Type') }}</span>
                    <select name="type">
                        <option value="">{{ __('All types') }}</option>
                        @foreach(TrxType::options() as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="dk-history-field">
                    <span>{{ __('Status') }}</span>
                    <select name="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach(TrxStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="dk-history-field">
                    <span>{{ __('Date range') }}</span>
                    <input type="text"
                           name="daterange"
                           value="{{ request('daterange') }}"
                           placeholder="{{ __('YYYY-MM-DD,YYYY-MM-DD') }}">
                </label>

                <div class="dk-history-filter__actions">
                    <a href="{{ route('user.transaction.index') }}" class="dk-history-filter__reset">
                        {{ __('Reset') }}
                    </a>
                    <button type="submit" class="dk-history-filter__submit">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        {{ __('Apply') }}
                    </button>
                </div>
            </form>
        </details>

        <div class="dk-history-list">
            @if($mobileTransactions->isEmpty())
                <x-user-not-found
                    :title="__('No transactions found')"
                    :message="__('Try adjusting the filters or search terms to find matching wallet activity.')"
                    :eyebrow="__('Transaction stream')"
                    icon="fa-receipt"
                    class="dk-history-empty"
                >
                    <x-slot:preview>
                        <div class="dk-history-empty__preview">
                            <span><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i></span>
                            <div>
                                <strong>{{ __('No activity') }}</strong>
                                <small>{{ __('0 records') }}</small>
                            </div>
                        </div>
                    </x-slot:preview>
                </x-user-not-found>
            @else
                @foreach($mobileGroups as $groupLabel => $groupTransactions)
                    <div class="dk-history-group">
                        <div class="dk-history-group__title">{{ $groupLabel }}</div>

                        @foreach($groupTransactions as $transaction)
                            @php
                                $transactionTypeClass = $transaction->trx_type->kebabCase();
                                $icon = $transaction->trx_type->icon();
                                $amountSign = $transaction->amount_flow->sign($transaction->status);
                                $flowTone = match (true) {
                                    $transaction->status !== TrxStatus::COMPLETED => $transaction->status->value,
                                    $transaction->amount_flow === AmountFlow::PLUS => 'in',
                                    $transaction->amount_flow === AmountFlow::MINUS => 'out',
                                    default => 'neutral',
                                };
                            @endphp

                            <button type="button"
                                    class="dk-history-row"
                                    data-tone="{{ $flowTone }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#transactionModal{{ $transaction->id }}">
                                <span class="dk-history-row__icon {{ $transactionTypeClass }}" aria-hidden="true">
                                    <x-icon name="{{ $icon }}" height="18" width="18"/>
                                </span>

                                <span class="dk-history-row__body">
                                    <span class="dk-history-row__main">
                                        <span class="dk-history-row__title">{{ $transaction->description }}</span>
                                        <span class="dk-history-row__amount">
                                            {{ $amountSign.number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                                        </span>
                                    </span>
                                    <span class="dk-history-row__meta">
                                        <span class="dk-history-row__type">{{ $transaction->trx_type->label() }}</span>
                                        <span class="dk-history-row__dot"></span>
                                        <span>{{ $transaction->created_at->format('h:i A') }}</span>
                                    </span>
                                    <span class="dk-history-row__foot">
                                        <span class="dk-history-row__trx">{{ strtoupper($transaction->trx_id) }}</span>
                                        <span class="dk-history-row__status dk-history-row__status--{{ $transaction->status->value }}">
                                            <i class="{{ $transaction->status->icon() }}" aria-hidden="true"></i>
                                            {{ $transaction->status->label() }}
                                        </span>
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>

        @if($transactions->hasPages())
            <div class="dk-history-pagination">
                {{ $transactions->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

    <div class="user-dashboard user-transaction-modals">
        @foreach($transactions as $transaction)
            @php($transactionTypeClass = $transaction->trx_type->kebabCase())
            @include('frontend.user.transaction.partials._details_modal', ['transaction' => $transaction, 'transactionTypeClass' => $transactionTypeClass])
        @endforeach
    </div>
@endsection
