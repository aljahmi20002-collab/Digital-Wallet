@extends('backend.wallet_earn.layout')

@section('title', __('Edit Wallet Earn Plan'))
@section('wallet_earn_title', __('Edit Plan'))
@section('wallet_earn_icon', 'manage')
@section('wallet_earn_subtitle', __('Tune earning rules without disturbing existing stake snapshots.'))

@section('wallet_earn_content')
    <form action="{{ route('admin.wallet-earn.plans.update', $plan) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('backend.wallet_earn.plans._form')
    </form>
@endsection
