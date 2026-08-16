@extends('backend.layouts.app')
@section('title')
    {{ __('Settings') }}
@endsection
@section('content')
    <div class="d-flex justify-content-between align-items-center my-2">
        <div class="fs-3 fw-semibold">@yield('setting_title')</div>
        <div class="btn-toolbar">
            @yield('setting_action')
        </div>
    </div>

    <div class="card px-3 py-3">
        <div class="py-3">
            @yield('setting_content')
        </div>
    </div>
@endSection
