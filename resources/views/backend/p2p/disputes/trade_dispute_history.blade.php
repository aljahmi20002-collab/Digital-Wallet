@extends('backend.p2p.layout')

@section('title', __('Trade Dispute History'))

@section('p2p_title')
    {{ __('Dispute History') }}
@endsection

@section('p2p_icon', 'history')

@section('p2p_action')
    <a href="{{ route('admin.p2p.disputes.index') }}" class="fb-btn fb-btn--ghost">
        <x-icon name="back" height="14" width="14"/>
        @lang('Back to Open')
    </a>
@endsection

@php
    $historyTotal = method_exists($disputes, 'total') ? (int) $disputes->total() : $disputes->count();
@endphp

@section('p2p_content')
    <div class="fb-page fb-console">
        <section class="fb-card pa-table-card">
            <div class="fb-card__head">
                <div>
                    <span class="fb-hero__eyebrow">{{ __('Closed Cases') }}</span>
                    <h5>{{ __('Resolved & rejected disputes') }}</h5>
                </div>
                <div class="fb-card__meta">
                    <span class="fb-pill fb-pill--neutral">{{ __('Records') }} <b>{{ number_format($historyTotal) }}</b></span>
                </div>
            </div>

            <div class="fb-table table-responsive">
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>@lang('Dispute')</th>
                            <th>@lang('Order')</th>
                            <th>@lang('Parties')</th>
                            <th>@lang('Resolution')</th>
                            <th>@lang('Raised By')</th>
                            <th>@lang('Closed at')</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($disputes as $d)
                        @php
                            $maker = $d->order?->maker?->name ?? '-';
                            $taker = $d->order?->taker?->name ?? '-';
                            $isResolved = $d->status === \App\Enums\P2P\DisputeStatus::RESOLVED;
                        @endphp
                        <tr>
                            <td><span class="fb-id-chip">D-{{ str_pad((string) $d->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                            <td class="fb-num fb-text-soft">#{{ $d->order_id }}</td>
                            <td>
                                <div class="fb-parties">
                                    <span><b>{{ $maker }}</b></span>
                                    <span class="fb-parties__vs">VS</span>
                                    <span>{{ $taker }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="fb-pill {{ $isResolved ? 'fb-pill--success' : 'fb-pill--neutral' }} pa-pill {{ $isResolved ? 'pa-pill--success' : 'pa-pill--neutral' }} no-dot">
                                    {{ $d->status->label() }}
                                </span>
                                @if($d->resolution)
                                    <div class="fb-user__meta mt-1 fb-resolution-note">{{ \Illuminate\Support\Str::limit($d->resolution, 80) }}</div>
                                @endif
                            </td>
                            <td class="fb-text-muted">{{ $d->raiser?->name ?? $d->raised_by }}</td>
                            <td class="fb-mono fb-cell-age">{{ $d->updated_at->format('M d, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin-not-found
                                    :title="__('No dispute records found')"
                                    :message="__('Closed disputes will appear here once cases start being resolved.')"
                                    icon="fa-folder-open"
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
