@extends('frontend.layouts.user.index')
@section('title', __('Dashboard'))

@section('content')
    @include('frontend.user.dashboard.partials._mobile_app_dashboard')
    <div class="user-dashboard">
        @include('frontend.user.dashboard.partials._hero_overview')
        @if(isActive('user.settings.kyc.verify') !== 'active')
            @include('frontend.user.dashboard.partials._kyc_notice_card')
        @endif
        @include('frontend.user.dashboard.partials._quick_actions')
        @include('frontend.user.dashboard.partials._amount_card')
        @include('frontend.user.dashboard.partials._chart_card')
        @include('frontend.user.dashboard.partials._recent_transactions')
    </div>
@endsection

@push('scripts')
    @include('frontend.user.dashboard.partials._script')
    @include('frontend.user.dashboard.partials._mobile_cards_script')
@endpush
