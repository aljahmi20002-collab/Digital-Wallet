@php use App\Enums\UserStatus; @endphp
@php use App\Enums\KycStatus; @endphp
@extends('backend.layouts.app')
@section('title', $title)

@section('content')
    @php
        $pageUsers = $users->getCollection();
		$activeUsers = $pageUsers->filter(fn ($user): bool => $user->status === UserStatus::ACTIVE)->count();
		$verifiedUsers = $pageUsers->filter(fn ($user): bool => $user->email_verified_at !== null)->count();
		$approvedKycUsers = $pageUsers->filter(fn ($user): bool => $user->kycSubmission?->status === KycStatus::APPROVED)->count();
    @endphp
    
    <div class="admin-users-page">
        <section class="admin-users-hero">
            <div class="admin-users-hero__copy">
                <h1 class="admin-users-hero__title">{{ $title }}</h1>
            </div>

            @can('user-create')
                <a href="#new_user_modal" data-coreui-toggle="modal" class="admin-users-hero__action">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    <span>{{ __('Add New') }}</span>
                </a>
            @endcan
        </section>
        
        <div class="admin-users-stats" aria-label="{{ __('User list summary') }}">
            <div class="admin-users-stat">
                <span class="admin-users-stat__icon admin-users-stat__icon--primary">
                    <i class="fa-solid fa-address-book" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stat__value">{{ number_format($users->total()) }}</span>
                    <span class="admin-users-stat__label">{{ __('Total') }}</span>
                </div>
            </div>
            <div class="admin-users-stat">
                <span class="admin-users-stat__icon admin-users-stat__icon--success">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stat__value">{{ $activeUsers }}</span>
                    <span class="admin-users-stat__label">{{ __('Active on page') }}</span>
                </div>
            </div>
            <div class="admin-users-stat">
                <span class="admin-users-stat__icon admin-users-stat__icon--info">
                    <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stat__value">{{ $verifiedUsers }}</span>
                    <span class="admin-users-stat__label">{{ __('Email verified') }}</span>
                </div>
            </div>
            <div class="admin-users-stat">
                <span class="admin-users-stat__icon admin-users-stat__icon--warning">
                    <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="admin-users-stat__value">{{ $approvedKycUsers }}</span>
                    <span class="admin-users-stat__label">{{ __('KYC approved') }}</span>
                </div>
            </div>
        </div>
        
        <section class="admin-users-panel">
            @include('backend.user.partials._filters')
            
            <div class="admin-users-table-wrap">
                @if($users->isNotEmpty())
                    <table class="table admin-users-table mb-0">
                        <thead>
                        <tr>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Contact') }}</th>
                            <th>{{ __('Verification') }}</th>
                            <th>{{ __('Joined') }}</th>
                            <th>{{ __('Last Login') }}</th>
                            @can('user-manage')
                                <th class="text-end">{{ __('Action') }}</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            @php
                                $avatarData = getUserAvatarDetails($user->first_name, $user->last_name);

								$kycSubmission = $user->kycSubmission;
								$kycStatus = $kycSubmission?->status ?? null;

								$badgeColor = $kycStatus?->color() ?? 'danger';
								$statusText = $kycStatus?->label() ?? __('Not Submitted');
								$kycTitle = $kycSubmission?->kycTemplate->title ?? __('No Submission');
                            @endphp
                            <tr class="admin-users-row align-middle">
                                <td>
                                    <div class="admin-users-person">
                                        <div class="admin-users-avatar-wrap">
                                            @if($user->avatar)
                                                <img class="admin-users-avatar" src="{{ asset($user->avatar) }}"
                                                     alt="{{ $user->name }}" loading="lazy">
                                            @else
                                                <div class="admin-users-avatar {{ $avatarData['class'] }} text-white">
                                                    {{ $avatarData['initials'] }}
                                                </div>
                                            @endif
                                            <span class="admin-users-status-dot bg-{{ $user->status->color() }}"
                                                  title="{{ $user->status->label() }}"></span>
                                        </div>
                                        <div class="admin-users-person__copy">
                                            <a href="{{ route('admin.user.manage', ['username' => $user->username, 'param' => 'statistics']) }}"
                                               class="admin-users-name">
                                                {{ title($user->name) }}
                                            </a>
                                            <div class="admin-users-username">
                                                <span>{{ '@'.$user->username }}</span>
                                                <span class="admin-user-role-badge admin-user-role-badge--{{ $user->role->value }}">
                                                    {{ $user->role->title() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-users-contact">
                                        <div class="admin-users-contact__line">
                                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                                            <span>{{ maskSensitive($user->email) }}</span>
                                        </div>
                                        <span class="admin-users-pill admin-users-pill--{{ $user->email_verified_at ? 'success' : 'danger' }}">
                                            <i class="fa-solid {{ $user->email_verified_at ? 'fa-circle-check' : 'fa-circle-xmark' }}" aria-hidden="true"></i>
                                            {{ $user->email_verified_at ? __('Verified') : __('Unverified') }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-users-doc">
                                        <span class="admin-users-doc__label">
                                            {{ $kycSubmission ? $kycTitle : __('No KYC document submitted') }}
                                        </span>
                                        <div class="admin-users-doc__badges">
                                            <span class="admin-users-pill admin-users-pill--{{ $badgeColor }}">
                                                {{ $statusText }}
                                            </span>
                                            <span class="admin-users-pill admin-users-pill--document">
                                            {{ $kycSubmission ? __('Document') : __('KYC') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-users-date">
                                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                        <div>
                                            <span>{{ $user->created_at->format('Y-m-d H:i') }}</span>
                                            <small>{{ $user->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="admin-users-date {{ optional($user->latestLoginActivity)->login_at ? '' : 'is-empty' }}">
                                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                        <div>
                                            <span>{{ optional($user->latestLoginActivity)->login_at?->format('Y-m-d H:i') ?? '--' }}</span>
                                            <small>{{ optional($user->latestLoginActivity)->login_at?->diffForHumans() ?? '--' }}</small>
                                        </div>
                                    </div>
                                </td>
                                
                                @can('user-manage')
                                    <td class="text-end">
                                        <a href="{{ route('admin.user.manage', ['username' => $user->username, 'param' => 'statistics']) }}" class="admin-users-action">
                                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                                            <span>{{ __('Manage') }}</span>
                                        </a>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    
                    <div class="admin-users-pagination">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="admin-users-empty">
                        <x-admin-not-found
                            :title="__('No users found')"
                            :message="__('No user accounts match the current search or filter criteria.')"
                            icon="fa-users"
                        />
                    </div>
                @endif
            </div>
        </section>
    </div>
    
    {{-- Create New User Modal --}}
    @can('user-create')
        @include('backend.user.partials._new_user')
    @endcan

@endsection

@push('scripts')
    <script>
        "use strict";
        $('#countrySelect').on('change', function (e) {
            e.preventDefault();
            var countryCode = String($(this).val() || '').toUpperCase();
            var country = @json(array_values(getCountries()));
            var selectedCountry = country.find(function (item) {
                return String(item.code || '').toUpperCase() === countryCode;
            });
            $('#phone').html(selectedCountry && selectedCountry.dial_code ? selectedCountry.dial_code : '');
        });
    </script>
@endpush
