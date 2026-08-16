@extends('frontend.user.setting.index')
@section('title', __('Password & PIN'))

@section('user_setting_content')
    @php
        $hasPin = $hasPin ?? auth()->user()->hasWalletPin();
    @endphp

    @include('frontend.user.setting.partials._security_tabs')
    @include('frontend.user.setting.partials._wallet_pin_panel')
@endsection

@push('scripts')
    <script>
        "use strict";
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.wallet-pin-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    this.value = this.value.replace(/\D+/g, '').slice(0, 6);
                });
            });
        });
    </script>
@endpush
