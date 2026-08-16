@extends('backend.p2p.layout')

@section('title', __('Open Trade Disputes'))

@section('p2p_title')
    {{ __('Open Disputes') }}
@endsection

@section('p2p_icon', 'support')

@section('p2p_action')
    <a href="{{ route('admin.p2p.disputes.history') }}" class="fb-btn fb-btn--ghost">
        <x-icon name="history" height="14" width="14"/>
        @lang('History')
    </a>
@endsection

@php
    $openTotal = method_exists($disputes, 'total') ? (int) $disputes->total() : $disputes->count();
@endphp

@section('p2p_content')
    <div class="fb-page fb-console">
        <section class="fb-card pa-table-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">{{ __('Active Cases') }}</span>
                    <h5>{{ __('Disputes awaiting review') }}</h5>
                </div>
                <div class="fb-card__meta">
                    <span class="live-pill">{{ __('Live queue') }}</span>
                    <span class="fb-pill fb-pill--warning">{{ __('Open') }} <b>{{ number_format($openTotal) }}</b></span>
                </div>
            </div>

            <div class="fb-table table-responsive">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>@lang('Dispute')</th>
                            <th>@lang('Order')</th>
                            <th>@lang('Parties')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Open for')</th>
                            <th class="text-end">@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($disputes as $d)
                        @php
                            $maker = $d->order?->maker?->name ?? '-';
                            $taker = $d->order?->taker?->name ?? '-';
                        @endphp
                        <tr>
                            <td>
                                <span class="fb-id-chip">D-{{ str_pad((string) $d->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <a class="fb-num fb-link" href="{{ route('admin.p2p.disputes.show', $d) }}">#{{ $d->order_id }}</a>
                            </td>
                            <td>
                                <div class="fb-parties">
                                    <span><b>{{ $maker }}</b></span>
                                    <span class="fb-parties__vs">VS</span>
                                    <span>{{ $taker }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="fb-pill fb-pill--warning pa-pill pa-pill--warning">{{ $d->status->label() }}</span>
                            </td>
                            <td class="fb-mono fb-cell-age">{{ $d->created_at->diffForHumans(null, true) }}</td>
                            <td class="text-end">
                                <div class="fb-btn-group">
                                    <a href="{{ route('admin.p2p.disputes.show', $d) }}" class="fb-btn fb-btn--ghost fb-btn--sm">
                                        <x-icon name="eye" height="13" width="13"/>
                                        @lang('Open')
                                    </a>
                                    <a href="{{ route('admin.p2p.disputes.show', $d) }}" class="fb-btn fb-btn--primary fb-btn--sm">
                                        @lang('Review')
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin-not-found
                                    :title="__('All clear')"
                                    :message="__('No open trade disputes need attention right now.')"
                                    icon="fa-circle-check"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($disputes->hasPages())
                <div class="fb-card__footer pa-table__foot">{{ $disputes->links() }}</div>
            @endif
        </section>
    </div>
@endsection
