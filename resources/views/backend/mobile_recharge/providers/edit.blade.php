@extends('backend.mobile_recharge.layout')

@section('title', __('Edit Mobile Recharge Provider'))
@section('sub_title', $provider->name)
@section('sub_subtitle', __('Update provider identity, pricing, and default routing.'))
@section('sub_icon', 'plug-connect')

@section('sub_action')
    <a href="{{ route('admin.mobile-recharge.index', ['tab' => 'providers']) }}" class="btn btn-light d-inline-flex align-items-center gap-1">
        <x-icon name="back" height="16" width="16"/>
        @lang('Back')
    </a>
@endsection

@section('sub_content')
    @include('backend.mobile_recharge.providers._manage_append', ['provider' => $provider])
@endsection
