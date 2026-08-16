@extends('backend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/feature-management.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/css/mobile-recharge-admin.css?v=' . config('app.version')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('backend/js/mobile-recharge-admin.js?v=' . config('app.version')) }}"></script>
@endpush

@section('content')
    @php
        $pageIcon = trim($__env->yieldContent('sub_icon', 'mobile-recharge')) ?: 'mobile-recharge';
    @endphp

    <div class="mra-admin">
        <div class="mra-admin-header py-4">
            <div>
                <h1 class="mra-admin-header__title mb-1">
                    <span class="mra-admin-header__icon">
                        <x-icon :name="$pageIcon" height="24" width="24"/>
                    </span>
                    <span>@yield('sub_title')</span>
                </h1>
                <div class="text-muted small">@yield('sub_subtitle')</div>
            </div>

            @hasSection('sub_action')
                <div class="mra-admin-header__actions">
                    @yield('sub_action')
                </div>
            @endif
        </div>

        <div class="card border-0 mra-admin-shell px-3 py-4">
            <div class="mra-admin-body">
                @yield('sub_content')
            </div>
        </div>
    </div>
@endsection
