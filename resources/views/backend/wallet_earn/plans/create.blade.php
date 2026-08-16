@extends('backend.wallet_earn.layout')

@section('title', __('Create Wallet Earn Plan'))
@section('wallet_earn_title', __('Create Plan'))
@section('wallet_earn_icon', 'add')
@section('wallet_earn_subtitle', __('Add a new earning plan for one currency or every supported wallet currency.'))

@section('wallet_earn_content')
    <form action="{{ route('admin.wallet-earn.plans.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('backend.wallet_earn.plans._form')
    </form>
@endsection
