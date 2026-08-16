@extends('backend.p2p.layout')

@section('title', __('Promotions'))

@section('p2p_title')
    {{ __('Promotions') }}
@endsection

@section('p2p_icon', 'chart-up')

@section('p2p_content')
    @php
        $decimals      = (int) setting('site_decimal', 2);
        $isActive      = ($tab ?? 'active') === 'active';
        $thumbVariants = ['', 'promo-thumb--success', 'promo-thumb--warning', 'promo-thumb--danger', 'promo-thumb--neutral'];
        $hasActiveRows = $isActive ? ($promotions->count() > 0) : false;
        $hasPurchases  = ! $isActive ? ($purchases->count() > 0) : false;
        $hasFilters    = request()->hasAny(['status', 'offer_id', 'user_search', 'package_id', 'trx_id']);
    @endphp

    <div class="p2p-refresh">
        <section class="fb-card pa-table-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">{{ $isActive ? __('Live window') : __('Purchase ledger') }}</span>
                    <h5>{{ $isActive ? __('Active promotions') : __('Promotion purchase history') }}</h5>
                </div>
                @if(($isActive && $hasActiveRows) || (! $isActive && $hasPurchases))
                    <div class="fb-card__meta">
                        @if($isActive)
                            <span class="fb-pill fb-pill--success">
                                <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                                <span>{{ number_format($promotions->total() ?? $promotions->count()) }} @lang('active')</span>
                            </span>
                        @else
                            <span class="fb-pill fb-pill--neutral">
                                <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                                <span>{{ number_format($purchases->total() ?? $purchases->count()) }} @lang('purchases')</span>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Tabs --}}
            <div class="fb-toolbar">
                <div class="fb-segment" role="group" aria-label="{{ __('Promotion view') }}">
                    <a class="fb-segment__item {{ $isActive ? 'is-active' : '' }}"
                       href="{{ route('admin.p2p.promotions.index', ['tab' => 'active']) }}"
                       aria-pressed="{{ $isActive ? 'true' : 'false' }}">
                        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                        <span>@lang('Active promotions')</span>
                    </a>
                    <a class="fb-segment__item {{ ! $isActive ? 'is-active' : '' }}"
                       href="{{ route('admin.p2p.promotions.index', ['tab' => 'purchases']) }}"
                       aria-pressed="{{ ! $isActive ? 'true' : 'false' }}">
                        <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                        <span>@lang('Purchase history')</span>
                    </a>
                </div>
            </div>

            {{-- Filter row --}}
            <form method="GET" action="{{ route('admin.p2p.promotions.index') }}" class="promo-filter">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="promo-filter__row">
                    @if($isActive)
                        <div class="promo-filter__field">
                            <label for="promo_status">@lang('Status')</label>
                            <select name="status" id="promo_status">
                                <option value="">@lang('All statuses')</option>
                                <option value="ACTIVE" @selected(request('status') === 'ACTIVE')>@lang('Active')</option>
                                <option value="EXPIRED" @selected(request('status') === 'EXPIRED')>@lang('Expired')</option>
                                <option value="CANCELLED" @selected(request('status') === 'CANCELLED')>@lang('Cancelled')</option>
                            </select>
                        </div>
                    @endif
                    <div class="promo-filter__field">
                        <label for="promo_offer">@lang('Trade ad ID')</label>
                        <input type="number" name="offer_id" id="promo_offer" value="{{ request('offer_id') }}" min="1" placeholder="{{ __('e.g. 1234') }}">
                    </div>
                    <div class="promo-filter__field">
                        <label for="promo_user">@lang('User')</label>
                        <input type="text" name="user_search" id="promo_user" value="{{ request('user_search') }}" placeholder="{{ __('Username or email') }}">
                    </div>
                    <div class="promo-filter__field">
                        <label for="promo_plan">@lang('Plan ID')</label>
                        <input type="number" name="package_id" id="promo_plan" value="{{ request('package_id') }}" min="1" placeholder="{{ __('e.g. 7') }}">
                    </div>
                    <div class="promo-filter__field">
                        <label for="promo_trx">@lang('Transaction ID')</label>
                        <input type="text" name="trx_id" id="promo_trx" value="{{ request('trx_id') }}" placeholder="TRX-…">
                    </div>
                    <div class="promo-filter__actions">
                        <button class="fb-btn fb-btn--primary fb-btn--sm" type="submit">
                            <i class="fa-solid fa-filter" aria-hidden="true"></i>
                            <span>@lang('Apply')</span>
                        </button>
                        @if($hasFilters)
                            <a class="fb-btn fb-btn--ghost fb-btn--sm" href="{{ route('admin.p2p.promotions.index', ['tab' => $tab]) }}">
                                <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                                <span>@lang('Reset')</span>
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="fb-card__body fb-card__body--flush">
                @if($isActive)
                    @if($hasActiveRows)
                        <ul class="promo-list">
                            @foreach($promotions as $promotion)
                                @php
                                    $offerCurrency = $promotion->offer?->wallet?->currency?->code;
                                    $variant       = $thumbVariants[$promotion->id % count($thumbVariants)] ?? '';
                                    $thumbInitial  = strtoupper(substr((string) ($promotion->package?->name ?? 'P'), 0, 4));
                                @endphp
                                <li class="promo-row">
                                    <div class="promo-thumb {{ $variant }}">{{ $thumbInitial }}</div>
                                    <div class="promo-meta">
                                        <span class="promo-meta__title">{{ $promotion->package?->name ?? __('Plan #:n', ['n' => $promotion->package_id]) }}</span>
                                        <span class="promo-meta__desc">
                                            @lang('Trade ad') <span class="fb-mono">#{{ $promotion->offer_id }}</span>
                                            @if($offerCurrency) · {{ $offerCurrency }} @endif
                                            · {{ $promotion->user?->name ?? __('Deleted user') }}
                                            @if($promotion->user)
                                                ({{ $promotion->user->username ? '@'.$promotion->user->username : $promotion->user->email }})
                                            @endif
                                        </span>
                                        <div class="promo-meta__stats">
                                            <span>@lang('Paid') <b>{{ number_format((float) $promotion->paid_amount, $decimals) }} {{ $promotion->paid_currency }}</b></span>
                                            <span>@lang('Window') <b>{{ $promotion->starts_at?->format('M d, H:i') ?? '—' }} → {{ $promotion->ends_at?->format('M d, H:i') ?? '—' }}</b></span>
                                            <span>@lang('Plan') <b>#{{ $promotion->package_id }}</b></span>
                                        </div>
                                    </div>
                                    <div>
                                        @if($promotion->status)
                                            <span class="fb-pill fb-pill--success">
                                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                                <span>{{ $promotion->status->label() }}</span>
                                            </span>
                                        @else
                                            <span class="fb-pill fb-pill--neutral">
                                                <i class="fa-solid fa-circle-minus" aria-hidden="true"></i>
                                                <span>—</span>
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <x-admin-not-found
                            :title="$hasFilters ? __('No promotions match your filters') : __('No active promotions')"
                            :message="$hasFilters ? __('Try adjusting or clearing the filters above.') : __('When traders buy a promotion plan for their trade ad, it appears here.')"
                            icon="fa-chart-line"
                            :action-url="$hasFilters ? route('admin.p2p.promotions.index', ['tab' => 'active']) : null"
                            :action-label="$hasFilters ? __('Clear filters') : null"
                            action-icon="fa-arrow-rotate-left"
                        />
                    @endif
                @else
                    @if($hasPurchases)
                        <div class="fb-table table-responsive">
                            <table class="pa-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('Trade ad')</th>
                                        <th>@lang('User')</th>
                                        <th>@lang('Plan')</th>
                                        <th>@lang('Duration')</th>
                                        <th class="text-end">@lang('Paid')</th>
                                        <th>@lang('Window')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $purchase)
                                        @php
                                            $durationMinutes = (int) $purchase->duration_minutes;
                                            $durationText = $durationMinutes >= 60
                                                ? (rtrim(rtrim(number_format($durationMinutes / 60, 2, '.', ''), '0'), '.').' '.__('hours'))
                                                : ($durationMinutes.' '.__('min'));
                                            $offerCurrency = $purchase->offer?->wallet?->currency?->code;
                                        @endphp
                                        <tr>
                                            <td class="fb-mono">{{ $purchase->id }}</td>
                                            <td>
                                                <div class="fb-num">#{{ $purchase->offer_id }}</div>
                                                @if($offerCurrency)
                                                    <div style="font-size: var(--font-xs); color: var(--color-text-faint);">{{ $offerCurrency }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $purchase->user?->name ?? __('Deleted user') }}</div>
                                                @if($purchase->user)
                                                    <div style="font-size: var(--font-xs); color: var(--color-text-faint);">
                                                        {{ $purchase->user->username ? '@'.$purchase->user->username : $purchase->user->email }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $purchase->package?->name ?? '#'.$purchase->package_id }}</div>
                                                <div style="font-size: var(--font-xs); color: var(--color-text-faint);">#{{ $purchase->package_id }}</div>
                                            </td>
                                            <td class="fb-mono">{{ $durationText }}</td>
                                            <td class="text-end fb-num">
                                                {{ number_format((float) $purchase->paid_amount, $decimals) }}
                                                <span style="font-size: var(--font-xs); color: var(--color-text-faint);">{{ $purchase->paid_currency }}</span>
                                            </td>
                                            <td class="fb-mono" style="font-size: var(--font-xs);">
                                                <div>{{ $purchase->starts_at?->format('Y-m-d H:i') ?? '—' }}</div>
                                                <div>{{ $purchase->ends_at?->format('Y-m-d H:i') ?? '—' }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <x-admin-not-found
                            :title="$hasFilters ? __('No purchases match your filters') : __('No purchases yet')"
                            :message="$hasFilters ? __('Try adjusting or clearing the filters above.') : __('Promotion purchases will appear here once traders pay for a plan.')"
                            icon="fa-receipt"
                            :action-url="$hasFilters ? route('admin.p2p.promotions.index', ['tab' => 'purchases']) : null"
                            :action-label="$hasFilters ? __('Clear filters') : null"
                            action-icon="fa-arrow-rotate-left"
                        />
                    @endif
                @endif
            </div>

            @php
                $pager = $isActive ? $promotions : $purchases;
            @endphp
            @if($pager && $pager->hasPages())
                <div class="fb-card__footer pa-table__foot">
                    <span>{{ __('Showing :n of :t', ['n' => number_format(count($pager->items())), 't' => number_format($pager->total())]) }}</span>
                    {{ $pager->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
