@extends('backend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/custom-landing-admin.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    @php
        $pageIcon = trim($__env->yieldContent('sub_icon', 'custom-landing')) ?: 'custom-landing';
    @endphp

    <div class="cla-admin">
        <div class="cla-header py-4">
            <div class="cla-header__main">
                <h1 class="cla-header__title">
                    <span class="cla-header__icon">
                        <x-icon :name="$pageIcon" height="24" width="24"/>
                    </span>
                    <span>@yield('sub_title')</span>
                </h1>
                <div class="cla-header__subtitle">@yield('sub_subtitle')</div>
            </div>

            @hasSection('sub_action')
                <div class="cla-header__actions">
                    @yield('sub_action')
                </div>
            @endif
        </div>

        <div class="cla-shell">
            @yield('sub_content')
        </div>
    </div>
@endsection
