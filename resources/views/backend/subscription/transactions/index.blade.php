@extends('backend.subscription.layout')

@section('title', __('Subscription Transactions'))
@section('sub_title', __('Subscription Transactions'))
@section('sub_icon', 'transaction-2')
@section('sub_subtitle', __('All subscription payment records including new subscriptions and renewals.'))

@section('sub_action')
    <a href="{{ route('admin.subscription.user-subscriptions.index') }}"
       class="btn btn-light d-inline-flex align-items-center gap-1">
        <x-icon name="people" height="18" width="18"/>
        @lang('User Subscriptions')
    </a>
@endsection

@section('sub_content')

    {{-- Filter --}}
    <div class="sa-filter mb-4">
        <form method="GET" action="{{ route('admin.subscription.transactions') }}">
            <div class="sa-filter__row">
                <div class="sa-filter__field sa-filter__field--user">
                    <label class="form-label">@lang('Search User')</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="{{ __('Name, email') }}" value="{{ request('search') }}">
                </div>
                <div class="sa-filter__field">
                    <label class="form-label">@lang('Plan')</label>
                    <select name="plan_id" class="form-select form-select-sm">
                        <option value="">@lang('All Plans')</option>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" @selected(request('plan_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sa-filter__field">
                    <label class="form-label">@lang('Type')</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">@lang('All')</option>
                        <option value="new"     @selected(request('type') === 'new')>@lang('New')</option>
                        <option value="renewal" @selected(request('type') === 'renewal')>@lang('Renewal')</option>
                        <option value="refund"  @selected(request('type') === 'refund')>@lang('Refund')</option>
                    </select>
                </div>
                <div class="sa-filter__field">
                    <label class="form-label">@lang('Date')</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                </div>
                <div class="sa-filter__actions">
                    <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                        <x-icon name="filter" height="14" width="14"/>
                        @lang('Filter')
                    </button>
                    @if(request()->hasAny(['search','plan_id','type','date']))
                        <a href="{{ route('admin.subscription.transactions') }}" class="btn btn-sm btn-light">@lang('Reset')</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="sa-table-card">
        <div class="table-responsive">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('User')</th>
                        <th>@lang('Plan')</th>
                        <th>@lang('Type')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('TRX ID')</th>
                        <th>@lang('Date')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td class="sa-muted">{{ $trx->id }}</td>
                            <td>
                                <div class="sa-user">
                                    <span class="sa-user__name">{{ $trx->user->name }}</span>
                                    <span class="sa-user__email">{{ $trx->user->email }}</span>
                                </div>
                            </td>
                            <td style="font-size:.88rem;">{{ $trx->plan->name }}</td>
                            <td>
                                @php
                                    $typeColor = match($trx->type) {
                                        'new'     => 'primary',
                                        'renewal' => 'info',
                                        'refund'  => 'warning',
                                        default   => 'secondary',
                                    };
                                @endphp
                                <span class="sa-pill sa-pill--{{ $typeColor }}">{{ ucfirst($trx->type) }}</span>
                            </td>
                            <td class="fw-semibold" style="font-size:.88rem;">
                                {{ $trx->currency_code }} {{ number_format($trx->amount, 2) }}
                            </td>
                            <td class="sa-muted font-monospace" style="font-size:.82rem;">{{ $trx->trx_id ?? '—' }}</td>
                            <td class="sa-muted" style="font-size:.84rem; white-space:nowrap;">
                                {{ $trx->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin-not-found
                                    :title="__('No transactions found')"
                                    :message="__('Subscription payment records matching the current filters will appear here.')"
                                    icon="fa-receipt"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="sa-table-card__footer">{{ $transactions->links() }}</div>
        @endif
    </div>

@endsection
