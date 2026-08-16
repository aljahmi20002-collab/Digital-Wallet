@extends('backend.layouts.app')

@section('title', __('Role Create'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/role-management.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    @include('backend.role.partials._form', [
        'action' => route('admin.role.store'),
        'permissions' => $permissions,
        'submitLabel' => __('Create Role'),
    ])
@endsection
