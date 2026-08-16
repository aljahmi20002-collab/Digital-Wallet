@extends('backend.subscription.layout')

@section('title', __('User Subscriptions'))
@section('sub_title', __('User Subscriptions'))
@section('sub_icon', 'people')
@section('sub_subtitle', __('View, filter, and manage all user subscription records.'))

@section('sub_action')
    <a href="{{ route('admin.subscription.transactions') }}"
       class="btn btn-light d-inline-flex align-items-center gap-1">
        <x-icon name="transaction-2" height="18" width="18"/>
        @lang('Transactions')
    </a>
@endsection

@section('sub_content')

    {{-- Filter --}}
    <div class="sa-filter mb-4">
        <form method="GET" action="{{ route('admin.subscription.user-subscriptions.index') }}">
            <div class="sa-filter__row">
                <div class="sa-filter__field sa-filter__field--user">
                    <label class="form-label">@lang('Search User')</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="{{ __('Name, email, username') }}" value="{{ request('search') }}">
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
                    <label class="form-label">@lang('Status')</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">@lang('All')</option>
                        @foreach(\App\Enums\SubscriptionStatus::options() as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
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
                    @if(request()->hasAny(['search','plan_id','status','date']))
                        <a href="{{ route('admin.subscription.user-subscriptions.index') }}"
                           class="btn btn-sm btn-light">@lang('Reset')</a>
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
                        <th>@lang('Status')</th>
                        <th>@lang('Period')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Subscribed')</th>
                        <th class="text-end">@lang('Actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td class="sa-muted">{{ $sub->id }}</td>
                            <td>
                                <div class="sa-user">
                                    <span class="sa-user__name">{{ $sub->user->name }}</span>
                                    <span class="sa-user__email">{{ $sub->user->email }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:.88rem;">{{ $sub->plan->name }}</div>
                                <div class="sa-muted">{{ $sub->billing_cycle?->label() ?? __('Custom') }}</div>
                            </td>
                            <td>
                                <span class="sa-pill sa-pill--{{ $sub->status->badgeColor() }}">
                                    {{ $sub->status->label() }}
                                </span>
                            </td>
                            <td style="font-size:.84rem;">
                                @if($sub->current_period_end)
                                    <div>{{ __('Ends') }}: {{ $sub->current_period_end->format('d M Y') }}</div>
                                    @if($sub->isActive())
                                        <div class="sa-muted">{{ $sub->daysRemaining() }} @lang('days left')</div>
                                    @endif
                                @elseif($sub->billing_cycle?->isLifetime())
                                    <span class="sa-pill sa-pill--success">@lang('Lifetime')</span>
                                @else
                                    <span class="sa-muted">—</span>
                                @endif
                            </td>
                            <td style="font-size:.88rem;">
                                {{ $sub->currency_code }} {{ number_format($sub->amount_paid, 2) }}
                            </td>
                            <td class="sa-muted" style="font-size:.84rem; white-space:nowrap;">
                                {{ $sub->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.subscription.user-subscriptions.show', $sub) }}"
                                   class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                    <x-icon name="eye" height="14" width="14"/>
                                    @lang('View')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin-not-found
                                    :title="__('No subscriptions found')"
                                    :message="__('User subscriptions matching the current filters will appear here.')"
                                    icon="fa-users"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="sa-table-card__footer">{{ $subscriptions->links() }}</div>
        @endif
    </div>

@endsection
