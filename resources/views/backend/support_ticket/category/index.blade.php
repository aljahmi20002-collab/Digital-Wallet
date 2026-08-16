@extends('backend.layouts.app')
@section('title')
    {{ __('Support Category') }}
@endsection
@section('content')
    <div class="py-4">
        <div class="d-flex justify-content-between w-100 flex-wrap">
            <div class="mb-3 mb-lg-0">
                <h1 class="h4">{{ __('Support Category') }}</h1>
            </div>
            <div class="btn-toolbar mb-md-0 mb-2">
                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center"
                        data-coreui-toggle="modal" data-coreui-target="#new-category-modal">
                    <x-icon name="add" class="me-1" height="24"/>
                    {{ __('Add New') }}
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table user-table align-items-center">
                    <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>
                                <span class="badge bg-{{ $category->status ? 'success' : 'danger' }}">{{ strtoupper($category->status ? 'ACTIVE' : 'INACTIVE') }}</span>
                            </td>
                            <td>{{ $category->created_at->diffForHumans() }}</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Actions">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary edit-modal"
                                       data-edit-url="{{ route('admin.support-ticket.category.edit', $category->id) }}">
                                        <x-icon name="edit" height="20"/> {{ __('Edit') }}
                                    </a>
                                    <a href="javascript:void(0)" class="btn btn-sm btn-danger text-white delete"
                                       data-url="{{ route('admin.support-ticket.category.destroy', $category->id) }}">
                                        <x-icon name="delete-3" height="20"/> {{ __('Delete') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin-not-found
                                    :title="__('No support categories found')"
                                    :message="__('Add categories to organize incoming support tickets.')"
                                    icon="fa-tags"
                                />
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('backend.support_ticket.category.partial._new_category_modal')
    @include('backend.support_ticket.category.partial._edit_category_modal')
@endsection
@section('scripts')
    <script>
    'use strict';
        $(document).ready(function () {
            editFormByModal('edit-category-modal', 'edit-category-data');
        });
    </script>
@endsection
