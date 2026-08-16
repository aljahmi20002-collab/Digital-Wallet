@extends('backend.layouts.app')
@section('title')
   {{ __('Dashboard') }}
@endsection
@section('content')

    <div class="admin-dashboard">
        @php
            $sqNoticeAdmin = auth('admin')->user();
        @endphp
        @if($sqNoticeAdmin && method_exists($sqNoticeAdmin, 'hasDismissedNotice') && ! $sqNoticeAdmin->hasDismissedNotice('scheduler-and-queue-setup'))
            @include('backend.dashboard.partials._scheduler_queue_notice')
        @endif

        @isset($quickRequests)
            @include('backend.dashboard.partials._quick_requests')
        @endisset

        @can('dashboard-stats')
            @include('backend.dashboard.partials._stats')
        @endcan

        @can('transactions-chart')
            @include('backend.dashboard.partials._transactions_chart')
        @endcan

        @can('wallet-balance')
            @include('backend.dashboard.partials._wallet_balance')
        @endcan

        <div class="row g-3 mb-3">
            @can('earning-chart')
                @include('backend.dashboard.partials._admin_earning_chart')
            @endcan

            @can('wallet-growth')
                @include('backend.dashboard.partials._wallet_growth')
            @endcan
        </div>

        <div class="row g-3 mb-3">
            @can('wallet-latest-transactions')
                @include('backend.dashboard.partials._latest_transactions')
            @endcan

            @can('wallet-latest-users')
                @include('backend.dashboard.partials._latest_users')
            @endcan
        </div>
    </div>
@endsection
