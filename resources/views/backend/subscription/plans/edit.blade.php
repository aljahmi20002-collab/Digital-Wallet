@extends('backend.subscription.layout')

@section('title', __('Edit Subscription Plan'))
@section('sub_title', __('Edit Plan: :name', ['name' => $plan->name]))
@section('sub_icon', 'manage')
@section('sub_subtitle', __('Update pricing, billing cycle, trial period, and feature limits.'))

@section('sub_action')
    <a href="{{ route('admin.subscription.plans.index') }}" class="btn btn-light d-inline-flex align-items-center gap-1">
        <x-icon name="back" height="18" width="18"/>
        @lang('Back to Plans')
    </a>
@endsection

@section('sub_content')
    <form action="{{ route('admin.subscription.plans.update', $plan) }}" method="POST" class="sa-form">
        @csrf @method('PUT')
        @include('backend.subscription.plans._form')
    </form>
@endsection
