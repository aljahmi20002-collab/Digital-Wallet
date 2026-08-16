@extends('backend.p2p.layout')

@section('title', __('Add Promotion Plan'))

@section('p2p_title')
    {{ __('Add Promotion Plan') }}
@endsection

@section('p2p_icon', 'apps')

@section('p2p_action')
    <a href="{{ route('admin.p2p.promotion-packages.index') }}" class="fb-btn fb-btn--ghost fb-btn--sm">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        @lang('Back')
    </a>
@endsection

@section('p2p_content')
    <div class="p2p-settings">
    <form method="POST" action="{{ route('admin.p2p.promotion-packages.store') }}" class="p2p-settings-card">
        @csrf

        @include('backend.p2p.promotion_plans._plan_form', [
            'package' => $package,
            'durationValue' => $durationValue,
            'durationUnit' => $durationUnit,
            'submitLabel' => __('Create Plan'),
        ])
    </form>
    </div>
@endsection
