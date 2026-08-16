@extends('backend.layouts.app')
@section('title', __('Gift Cards'))

@section('content')
    @php
        /*
         * Status registry — drives both the filter <select> and each
         * row's pill. Each entry pairs a label with the design token
         * suffix used by .gc-admin-pill (success / warning / info /
         * primary / secondary / danger) so the row colour stays in
         * sync with the meaning of the status.
         */
        $statusMap = [
            'pending'   => ['label' => __('Pending'),   'cls' => 'warning'],
            'scheduled' => ['label' => __('Scheduled'), 'cls' => 'info'],
            'delivered' => ['label' => __('Delivered'), 'cls' => 'success'],
            'redeemed'  => ['label' => __('Redeemed'),  'cls' => 'primary'],
            'expired'   => ['label' => __('Expired'),   'cls' => 'secondary'],
            'cancelled' => ['label' => __('Cancelled'), 'cls' => 'danger'],
        ];
    @endphp

    <div class="gift-card-admin">

        {{-- Page header — eyebrow + title + subtitle on the left,
             primary action button on the right. Matches the
             template manager page so both Gift Card admin screens
             share the same visual identity. --}}
        <div class="gift-card-admin__header">
            <div class="gift-card-admin__header-text">
                <span class="gift-card-admin__eyebrow">
                    <i class="fa-solid fa-gift" aria-hidden="true"></i>
                    {{ __('Gift Cards') }}
                </span>
                <h1 class="gift-card-admin__title">{{ __('All Gift Cards') }}</h1>
                <p class="gift-card-admin__subtitle">{{ __('Every card issued, delivered, redeemed, or cancelled across the platform.') }}</p>
            </div>
            @can('gift-card-template-list')
                <a href="{{ route('admin.gift-card-templates.index') }}" class="btn btn-primary gift-card-admin__action">
                    <x-icon name="apps" width="18" height="18"/>
                    <span>{{ __('Manage Templates') }}</span>
                </a>
            @endcan
        </div>

        {{-- KPI strip --}}
        <div class="row g-3 mb-4 gift-card-admin-kpis">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#DBEAFE; --kpi-fg:#1D4ED8;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="gift-card-admin-kpi__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 12 20 22 4 22 4 12"/>
                                <rect x="2" y="7" width="20" height="5"/>
                                <line x1="12" y1="22" x2="12" y2="7"/>
                                <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                                <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                            </svg>
                        </span>
                        <div>
                            <div class="gift-card-admin-kpi__label">{{ __('Total') }}</div>
                            <div class="gift-card-admin-kpi__value">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#DCFCE7; --kpi-fg:#15803D;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="gift-card-admin-kpi__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                        </span>
                        <div>
                            <div class="gift-card-admin-kpi__label">{{ __('Delivered') }}</div>
                            <div class="gift-card-admin-kpi__value">{{ number_format($stats['delivered']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#FCE7F3; --kpi-fg:#BE185D;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="gift-card-admin-kpi__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                        </span>
                        <div>
                            <div class="gift-card-admin-kpi__label">{{ __('Redeemed') }}</div>
                            <div class="gift-card-admin-kpi__value">{{ number_format($stats['redeemed']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 h-100 gift-card-admin-kpi" style="--kpi-bg:#FEF3C7; --kpi-fg:#92400E;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="gift-card-admin-kpi__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="13" rx="2"/>
                                <path d="M2 11h20"/>
                                <path d="M7 16h3"/>
                            </svg>
                        </span>
                        <div>
                            <div class="gift-card-admin-kpi__label">{{ __('Total value') }}</div>
                            <div class="gift-card-admin-kpi__value">{{ siteCurrency('symbol') ?? '$' }}{{ number_format($stats['value'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter + table --}}
        <div class="card border-0 mb-4 gift-card-admin__card">
            <div class="card-body">
                <form method="GET" class="gift-card-admin__filter mb-3">
                    <div class="gift-card-admin__filter-search">
                        <i class="fa-solid fa-magnifying-glass gift-card-admin__filter-search-icon" aria-hidden="true"></i>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               class="form-control gift-card-admin__filter-input"
                               placeholder="{{ __('Search by code, name, or email') }}">
                    </div>
                    <select name="status" class="form-select gift-card-admin__filter-select">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach($statusMap as $key => $meta)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary gift-card-admin__filter-submit" type="submit">
                        <x-icon name="search" width="16" height="16"/>
                        <span>{{ __('Filter') }}</span>
                    </button>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 gc-admin-table">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Sender') }}</th>
                                <th>{{ __('Recipient') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($giftCards as $card)
                                @php $meta = $statusMap[$card->status] ?? ['label' => ucfirst($card->status), 'cls' => 'secondary']; @endphp
                                <tr>
                                    <td>
                                        <span class="gc-admin-code" title="{{ $card->code }}">
                                            <i class="fa-solid fa-hashtag gc-admin-code__hash" aria-hidden="true"></i>
                                            {{ $card->code }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="gc-admin-person">
                                            <span class="gc-admin-person__name">{{ $card->sender_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="gc-admin-person">
                                            <span class="gc-admin-person__name">{{ $card->recipient_name }}</span>
                                            <span class="gc-admin-person__meta">{{ $card->recipient_email }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="gc-admin-amount">
                                            <span class="gc-admin-amount__sym">{{ $card->currency?->symbol ?? '$' }}</span>{{ number_format($card->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="gc-admin-pill gc-admin-pill--{{ $meta['cls'] }}">
                                            <span class="gc-admin-pill__dot" aria-hidden="true"></span>
                                            {{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="gc-admin-date">
                                            <span class="gc-admin-date__main">{{ $card->created_at->format('M d, Y') }}</span>
                                            <span class="gc-admin-date__meta">{{ $card->created_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="gc-admin-actions">
                                            <a href="{{ route('gift-card.preview', $card->code) }}"
                                               target="_blank"
                                               class="btn btn-outline-primary btn-sm gc-admin-btn"
                                               title="{{ __('Open preview in a new tab') }}">
                                                <x-icon name="arrow-up-right" width="14" height="14"/>
                                                <span>{{ __('View') }}</span>
                                            </a>

                                            @can('gift-card-manage')
                                                @if(in_array($card->status, ['pending', 'scheduled', 'delivered'], true))
                                                    <form action="{{ route('admin.gift-cards.cancel', $card->id) }}"
                                                          method="POST"
                                                          class="gc-admin-actions__form">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-outline-danger btn-sm gc-admin-btn"
                                                                onclick="return confirm('{{ __('Cancel this gift card?') }}')"
                                                                title="{{ __('Cancel this gift card') }}">
                                                            <x-icon name="x-circle" width="14" height="14"/>
                                                            <span>{{ __('Cancel') }}</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <x-admin-not-found
                                            :title="__('No gift cards yet')"
                                            :message="__('Once users start sending gift cards, every transaction will appear here.')"
                                            icon="fa-gift"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($giftCards->hasPages())
                    <div class="mt-3">
                        {{ $giftCards->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* ──────────────────────────────────────────────────────────
               Gift Card admin — premium polish shared across All Gift
               Cards + Template Manager. Scoped to .gift-card-admin so
               the global admin layout is untouched.
               ────────────────────────────────────────────────────────── */

            /* Section header — eyebrow + title + subtitle on the left,
               primary action button on the right. */
            .gift-card-admin__header {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 16px;
                flex-wrap: wrap;
                padding: 18px 0 22px;
            }
            .gift-card-admin__header-text { min-width: 0; }
            .gift-card-admin__eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 10.5px;
                font-weight: 800;
                color: #1D4ED8;
                background: #EFF6FF;
                padding: 4px 10px;
                border-radius: 999px;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                margin-bottom: 8px;
            }
            .gift-card-admin__eyebrow i { font-size: 10px; }
            .gift-card-admin__title {
                margin: 0;
                font-size: 1.45rem;
                font-weight: 800;
                color: #0F172A;
                letter-spacing: -0.02em;
                line-height: 1.15;
            }
            .gift-card-admin__subtitle {
                margin: 6px 0 0;
                color: #64748B;
                font-size: 0.825rem;
                font-weight: 500;
            }
            .gift-card-admin__action {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 9px 16px;
                font-size: 0.825rem;
                font-weight: 700;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(29, 78, 216, 0.18);
            }

            /* KPI cards */
            .gift-card-admin-kpi {
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 1px 1px rgba(15, 23, 42, .03);
                border-radius: 14px;
                transition: transform 0.18s ease, box-shadow 0.18s ease;
            }
            .gift-card-admin-kpi:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 24px -12px rgba(15, 23, 42, 0.12), 0 4px 8px -4px rgba(15, 23, 42, 0.06);
            }
            .gift-card-admin-kpi__icon {
                display: inline-grid;
                place-items: center;
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: var(--kpi-bg, #F1F5F9);
                color: var(--kpi-fg, #475569);
                flex-shrink: 0;
            }
            .gift-card-admin-kpi__icon svg { width: 22px; height: 22px; }
            .gift-card-admin-kpi__label {
                font-size: 11.5px;
                color: #64748B;
                font-weight: 700;
                line-height: 1.2;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }
            .gift-card-admin-kpi__value {
                font-size: 22px;
                font-weight: 800;
                letter-spacing: -0.02em;
                color: #0F172A;
                line-height: 1.15;
                margin-top: 4px;
                font-variant-numeric: tabular-nums;
            }

            /* Card wrapper around the table */
            .gift-card-admin__card {
                border-radius: 14px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 1px 1px rgba(15, 23, 42, .03);
            }
            .gift-card-admin__card > .card-body {
                padding: 18px 20px 20px;
            }

            /* Filter bar — search input with leading icon, status select,
               submit button. Sits on the same baseline. */
            .gift-card-admin__filter {
                display: grid;
                grid-template-columns: 1fr auto auto;
                gap: 10px;
                align-items: stretch;
            }
            @media (max-width: 575.98px) {
                .gift-card-admin__filter { grid-template-columns: 1fr; }
            }
            .gift-card-admin__filter-search { position: relative; }
            .gift-card-admin__filter-search-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #94A3B8;
                font-size: 13px;
                pointer-events: none;
            }
            .gift-card-admin__filter-input {
                height: 40px;
                padding-left: 38px;
                border-radius: 10px;
                border-color: #E6EAF3;
                font-size: 0.85rem;
                box-shadow: none;
            }
            .gift-card-admin__filter-input:focus {
                border-color: #93C5FD;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
            }
            .gift-card-admin__filter-select {
                height: 40px;
                min-width: 160px;
                border-radius: 10px;
                border-color: #E6EAF3;
                font-size: 0.85rem;
                font-weight: 600;
                color: #1E293B;
                box-shadow: none;
            }
            .gift-card-admin__filter-select:focus {
                border-color: #93C5FD;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
            }
            .gift-card-admin__filter-submit {
                height: 40px;
                padding: 0 18px;
                border-radius: 10px;
                font-size: 0.825rem;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            /* ─── Table ────────────────────────────────────────────────── */
            .gc-admin-table { margin: 0; }
            .gc-admin-table thead th {
                background: #F8FAFC;
                color: #64748B;
                font-size: 10.5px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                padding: 12px 14px;
                border-bottom: 1px solid #E6EAF3;
                border-top: 0;
                white-space: nowrap;
            }
            .gc-admin-table thead th:first-child { border-top-left-radius: 10px; }
            .gc-admin-table thead th:last-child  { border-top-right-radius: 10px; }
            .gc-admin-table tbody td {
                padding: 14px;
                vertical-align: middle;
                border-top: 1px solid #EEF1F7;
                font-size: 0.85rem;
                color: #0F172A;
            }
            .gc-admin-table tbody tr {
                transition: background-color 0.12s ease;
            }
            .gc-admin-table tbody tr:hover td {
                background-color: #FAFBFE;
            }

            /* Code chip */
            .gc-admin-code {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 5px 10px 5px 8px;
                border-radius: 7px;
                background: #F1F5F9;
                color: #1E293B;
                font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
                font-size: 0.74rem;
                font-weight: 700;
                letter-spacing: 0.02em;
                border: 1px solid #E2E8F0;
            }
            .gc-admin-code__hash { color: #94A3B8; font-size: 0.7rem; }

            /* Person / recipient cell — name on top, meta dimmed below */
            .gc-admin-person { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
            .gc-admin-person__name {
                font-weight: 600;
                color: #0F172A;
                font-size: 0.85rem;
                line-height: 1.25;
            }
            .gc-admin-person__meta {
                color: #94A3B8;
                font-size: 0.72rem;
                font-weight: 500;
                line-height: 1.2;
            }

            /* Amount cell — bold tabular numerals with softer symbol */
            .gc-admin-amount {
                font-weight: 800;
                font-size: 0.92rem;
                color: #0F172A;
                font-variant-numeric: tabular-nums;
                letter-spacing: -0.01em;
            }
            .gc-admin-amount__sym { color: #94A3B8; font-weight: 700; margin-right: 1px; }

            /* Date cell — two-line stack */
            .gc-admin-date { display: flex; flex-direction: column; gap: 2px; line-height: 1.2; }
            .gc-admin-date__main { color: #0F172A; font-weight: 600; font-size: 0.81rem; }
            .gc-admin-date__meta { color: #94A3B8; font-size: 0.7rem; font-weight: 500; }

            /* Status pill — soft pastel background, dot indicator, and a
               coloured ring so the chip looks like a proper status token
               rather than a flat Bootstrap badge. */
            .gc-admin-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px 4px 8px;
                border-radius: 999px;
                font-size: 0.72rem;
                font-weight: 700;
                line-height: 1;
                letter-spacing: 0.01em;
                white-space: nowrap;
            }
            .gc-admin-pill__dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: currentColor;
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.6);
                flex-shrink: 0;
            }
            .gc-admin-pill--success  { background: #DCFCE7; color: #15803D; }
            .gc-admin-pill--warning  { background: #FEF3C7; color: #92400E; }
            .gc-admin-pill--danger   { background: #FEE2E2; color: #B91C1C; }
            .gc-admin-pill--info     { background: #DBEAFE; color: #1D4ED8; }
            .gc-admin-pill--primary  { background: #EDE9FE; color: #6D28D9; }
            .gc-admin-pill--secondary{ background: #E2E8F0; color: #475569; }

            /* Actions column — compact outlined buttons that don't
               compete with the row content for visual weight. The
               Cancel button is wrapped in a <form> (for the POST
               action), so we make the form itself part of the flex
               flow with zero internal margin to keep the visual
               gap between buttons consistent. */
            .gc-admin-actions {
                display: inline-flex;
                gap: 8px;
                justify-content: flex-end;
                align-items: center;
            }
            .gc-admin-actions__form { display: inline-flex; margin: 0; }
            .gc-admin-btn {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 6px 11px;
                font-size: 0.74rem;
                font-weight: 700;
                border-radius: 8px;
                line-height: 1;
                border-width: 1px;
            }
            .gc-admin-btn svg { flex-shrink: 0; }
            .gc-admin-btn.btn-outline-primary { border-color: #DBEAFE; color: #1D4ED8; background: #fff; }
            .gc-admin-btn.btn-outline-primary:hover {
                background: #1D4ED8;
                color: #fff;
                border-color: #1D4ED8;
                box-shadow: 0 4px 10px rgba(29, 78, 216, 0.22);
            }
            .gc-admin-btn.btn-outline-danger { border-color: #FECACA; color: #B91C1C; background: #fff; }
            .gc-admin-btn.btn-outline-danger:hover {
                background: #DC2626;
                color: #fff;
                border-color: #DC2626;
                box-shadow: 0 4px 10px rgba(220, 38, 38, 0.22);
            }
        </style>
    @endpush
@endsection
