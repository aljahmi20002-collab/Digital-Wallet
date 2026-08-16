@extends('backend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/payment-link-admin.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    @php
        $pageIcon = trim($__env->yieldContent('sub_icon', 'payment')) ?: 'payment';
    @endphp

    <div class="pla-admin">
        <div class="pla-admin-header py-4">
            <div>
                <h1 class="pla-admin-header__title mb-1">
                    <span class="pla-admin-header__icon">
                        <x-icon :name="$pageIcon" height="24" width="24"/>
                    </span>
                    <span>@yield('sub_title')</span>
                </h1>
                <div class="text-muted small">@yield('sub_subtitle')</div>
            </div>

            @hasSection('sub_action')
                <div class="pla-admin-header__actions">
                    @yield('sub_action')
                </div>
            @endif
        </div>

        <div class="card border-0 pla-admin-shell px-3 py-4">
            <div class="pla-admin-body">
                @yield('sub_content')
            </div>
        </div>
    </div>
@endsection
