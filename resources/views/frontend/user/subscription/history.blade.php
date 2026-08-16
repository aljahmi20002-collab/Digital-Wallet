@extends('frontend.layouts.user.index')

@section('title', __('Subscription History'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/subscription.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="single-form-card">
                <x-user-feature-header
                    :title="__('Subscription History')"
                    :subtitle="__('All your past and present subscription records.')"
                    icon="fas fa-clock-rotate-left"
                >
                    <a href="{{ route('user.subscription.plans') }}" class="btn btn-light-primary btn-sm">
                        <i class="fa-solid fa-layer-group"></i> {{ __('View Plans') }}
                    </a>
                    <a href="{{ route('user.subscription.current') }}" class="btn btn-light-secondary btn-sm">
                        <i class="fa-solid fa-circle-check"></i> {{ __('Current') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main">
                    @if($subscriptions->isEmpty())
                        <x-user-not-found
                            :title="__('No subscription records found')"
                            :message="__('Your subscription activations, renewals, and plan changes will appear here.')"
                            icon="fa-clock-rotate-left"
                            :action-url="route('user.subscription.plans')"
                            :action-label="__('View Plans')"
                            action-icon="fa-layer-group"
                        />
                    @else
                        <div class="history-table">
                            <div class="table-list">
                                <ul class="list-header">
                                    <li>{{ __('Plan') }}</li>
                                    <li>{{ __('Status') }}</li>
                                    <li>{{ __('Amount') }}</li>
                                    <li>{{ __('Period') }}</li>
                                    <li>{{ __('Subscribed') }}</li>
                                </ul>
                            </div>

                            <div class="table-list">
                                @foreach($subscriptions as $sub)
                                    <ul class="list-content">
                                        <li>
                                            <div class="fw-semibold small">{{ $sub->plan->name }}</div>
                                            <div class="text-muted" style="font-size:.75rem;">{{ $sub->billing_cycle?->label() ?? __('Custom') }}</div>
                                        </li>
                                        <li>
                                            <span class="badge bg-{{ $sub->status->badgeColor() }}">{{ $sub->status->label() }}</span>
                                        </li>
                                        <li class="fw-semibold small">
                                            {{ $sub->currency_code }} {{ number_format($sub->amount_paid, 2) }}
                                        </li>
                                        <li class="small text-muted text-nowrap">
                                            @if($sub->current_period_end)
                                                {{ __('Ends') }}: {{ $sub->current_period_end->format('d M Y') }}
                                            @elseif($sub->billing_cycle?->isLifetime())
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    {{ __('Lifetime') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </li>
                                        <li class="small text-muted text-nowrap">
                                            {{ $sub->created_at->format('d M Y') }}
                                        </li>
                                    </ul>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($subscriptions->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $subscriptions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
