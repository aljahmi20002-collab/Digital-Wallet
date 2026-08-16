@extends('backend.layouts.app')

@section('title', __('Role Edit'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/role-management.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    @include('backend.role.partials._form', [
        'action' => route('admin.role.update', $role->id),
        'method' => 'PUT',
        'permissions' => $permissions,
        'role' => $role,
        'rolePermissions' => $rolePermissions,
        'submitLabel' => __('Update Role'),
    ])
@endsection
