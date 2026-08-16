@extends('backend.layouts.app')

@section('title', __('Roles'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/role-management.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    <div class="role-mgmt-page">
        <section class="role-mgmt-overview" aria-labelledby="role-mgmt-overview-title">
            <div class="role-mgmt-overview__main">
                <span class="role-mgmt-overview__icon">
                    <x-icon name="role" height="26" width="26"/>
                </span>
                <div class="role-mgmt-overview__copy">
                    <span class="role-mgmt-overview__eyebrow">
                        <span class="role-mgmt-overview__eyebrow-dot"></span>
                        {{ __('Access Control') }}
                    </span>
                    <h4 id="role-mgmt-overview-title">{{ __('Roles & Permissions') }}</h4>
                    <p class="role-mgmt-overview__lede">{{ __('Define scoped access, manage staff, and lock down sensitive areas.') }}</p>
                </div>
            </div>

            <div class="role-mgmt-overview__side">
                <div class="role-mgmt-overview__stats" role="group" aria-label="{{ __('Role summary') }}">
                    <div class="role-mgmt-stat role-mgmt-stat--roles">
                        <span class="role-mgmt-stat__icon" aria-hidden="true">
                            <x-icon name="role" height="16" width="16"/>
                        </span>
                        <span class="role-mgmt-stat__copy">
                            <strong>{{ number_format($summary['totalRoles'] ?? $roles->total()) }}</strong>
                            <small>{{ __('Total Roles') }}</small>
                        </span>
                    </div>
                    <div class="role-mgmt-stat role-mgmt-stat--staff">
                        <span class="role-mgmt-stat__icon" aria-hidden="true">
                            <x-icon name="kpi-users" height="16" width="16"/>
                        </span>
                        <span class="role-mgmt-stat__copy">
                            <strong>{{ number_format($summary['assignedStaff'] ?? 0) }}</strong>
                            <small>{{ __('Assigned Staff') }}</small>
                        </span>
                    </div>
                </div>

                @can('role-create')
                    <a href="{{ route('admin.role.create') }}" class="btn role-mgmt-header-action">
                        <x-icon name="add" height="18" width="18"/>
                        <span>{{ __('Add New') }}</span>
                    </a>
                @endcan
            </div>
        </section>

        <section class="role-mgmt-table-card">
            <div class="table-responsive">
                <table class="table role-mgmt-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Role') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                            <th scope="col">{{ __('Permissions') }}</th>
                            <th scope="col">{{ __('Staff') }}</th>
                            <th scope="col" class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            @php
                                $isProtected = $role->name === 'super-admin';
                                $currentRoleName = auth()->user()?->roles?->first()?->name;
                                $staffCount = (int) $role->users_count;
                            @endphp
                            <tr class="{{ $isProtected ? 'is-protected-row' : '' }}">
                                <td data-label="{{ __('Role') }}">
                                    <div class="role-mgmt-role">
                                        <span class="role-mgmt-role__icon {{ $isProtected ? 'is-protected' : 'is-custom' }}">
                                            <x-icon :name="$isProtected ? 'lock' : 'role'" height="20" width="20"/>
                                        </span>
                                        <span class="role-mgmt-role__content">
                                            <strong class="role-mgmt-role__name">{{ title($role->name) }}</strong>
                                            <span class="role-mgmt-staff">{{ $role->description ?: __('Operational access role') }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('Status') }}">
                                    <span class="role-mgmt-status {{ $isProtected ? 'is-protected' : 'is-custom' }}">
                                        <span class="role-mgmt-status__dot"></span>
                                        {{ $isProtected ? __('Protected') : __('Custom') }}
                                    </span>
                                </td>
                                <td data-label="{{ __('Permissions') }}">
                                    <span class="role-mgmt-count role-mgmt-count--permissions" title="{{ __(':n permissions granted', ['n' => number_format((int) $role->permissions_count)]) }}">
                                        <span class="role-mgmt-count__icon" aria-hidden="true">
                                            <x-icon name="role" height="13" width="13"/>
                                        </span>
                                        <span class="role-mgmt-count__value">{{ number_format((int) $role->permissions_count) }}</span>
                                    </span>
                                </td>
                                <td data-label="{{ __('Staff') }}">
                                    <div class="role-mgmt-staff-cell {{ $staffCount === 0 ? 'is-empty' : '' }}">
                                        @if($staffCount > 0)
                                            <div class="role-mgmt-avatar-stack">
                                                @foreach($role->users->take(3) as $user)
                                                    <span class="role-mgmt-avatar" data-coreui-toggle="tooltip" data-coreui-title="{{ $user->name ?? __('Staff') }}">
                                                        <img src="{{ asset($user->avatar_alt) }}" alt="{{ $user->name ?? __('Staff') }}" loading="lazy">
                                                    </span>
                                                @endforeach

                                                @if($staffCount > 3)
                                                    <span class="role-mgmt-avatar role-mgmt-avatar--count" aria-label="{{ __(':n more', ['n' => $staffCount - 3]) }}">
                                                        +{{ $staffCount - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="role-mgmt-staff-meta">
                                                <strong>{{ number_format($staffCount) }}</strong>
                                                <small>{{ trans_choice('member|members', $staffCount) }}</small>
                                            </span>
                                        @else
                                            <span class="role-mgmt-empty-pill">
                                                <span class="role-mgmt-empty-pill__dot" aria-hidden="true"></span>
                                                {{ __('Unassigned') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="{{ __('Actions') }}">
                                    <div class="role-mgmt-actions">
                                        @if(! $isProtected)
                                            @can('role-edit')
                                                <a href="{{ route('admin.role.edit', $role->id) }}"
                                                   class="btn role-mgmt-action-btn role-mgmt-action-btn--manage"
                                                   aria-label="{{ __('Manage :role', ['role' => title($role->name)]) }}"
                                                   title="{{ __('Manage role and permissions') }}">
                                                    <x-icon height="16" width="16" name="manage"/>
                                                    <span class="role-mgmt-action-btn__label">{{ __('Manage') }}</span>
                                                </a>
                                            @endcan
                                            @can('role-delete')
                                                @if($currentRoleName !== $role->name)
                                                    <a href="#"
                                                       class="btn role-mgmt-action-btn role-mgmt-action-btn--danger delete"
                                                       data-url="{{ route('admin.role.destroy', $role->id) }}"
                                                       aria-label="{{ __('Delete :role', ['role' => title($role->name)]) }}"
                                                       title="{{ __('Delete this role') }}">
                                                        <x-icon height="16" width="16" name="delete-2"/>
                                                        <span class="role-mgmt-action-btn__label">{{ __('Delete') }}</span>
                                                    </a>
                                                @endif
                                            @endcan
                                        @else
                                            <span class="role-mgmt-action-btn role-mgmt-action-btn--locked" title="{{ __('System role — cannot be edited') }}">
                                                <x-icon name="lock" height="16" width="16"/>
                                                <span class="role-mgmt-action-btn__label">{{ __('Protected') }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-admin-not-found
                                        :title="__('No roles found')"
                                        :message="__('Create a role to start assigning scoped staff permissions.')"
                                        icon="fa-user-shield"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
                <div class="role-mgmt-pagination">
                    {{ $roles->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
