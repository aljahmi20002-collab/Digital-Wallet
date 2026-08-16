@php
    $authUser     = auth()->user();
    $authName     = $authUser->name ?? __('Administrator');
    $authEmail    = $authUser->email ?? null;
    $authAvatar   = $authUser->avatar_alt ?? null;
    $authRoleText = __('Administrator');
@endphp
<header class="header header-sticky p-0 mb-4 admin-header admin-header--pro">
    <div class="container-fluid px-3 px-lg-4">
        <div class="admin-header-bar">
            <div class="admin-header-start">
                <button class="header-toggler admin-header-sidebar-toggle" type="button"
                        id="admin-header-sidebar-toggle"
                        aria-label="{{ __('Toggle sidebar') }}"
                        title="{{ __('Toggle sidebar') }}">
                    {{-- Menu icon (shown on small screens) --}}
                    <span class="sidebar-toggle-icon sidebar-toggle-icon--menu d-lg-none">
                        <x-icon name="cil-menu" class="icon icon-lg"/>
                    </span>
                    {{-- Collapse icon (shown when sidebar is expanded) --}}
                    <span class="sidebar-toggle-icon sidebar-toggle-icon--collapse">
                        <x-icon name="sidebar-collapse" class="icon icon-lg"/>
                    </span>
                    {{-- Expand icon (shown when sidebar is collapsed) --}}
                    <span class="sidebar-toggle-icon sidebar-toggle-icon--expand d-none">
                        <x-icon name="sidebar-expand" class="icon icon-lg"/>
                    </span>
                </button>

                <div class="admin-header-search-wrap">
                    @include('backend.layouts.partials._search')
                    <span class="admin-header-search-shortcut d-none d-lg-inline-flex" aria-hidden="true">
                        <kbd>Ctrl</kbd><span class="admin-header-search-shortcut__plus">+</span><kbd>K</kbd>
                    </span>
                </div>
            </div>

            <div class="admin-header-end">
                <ul class="header-nav admin-header-actions admin-header-actions--primary">

{{--             Admin notifications dropdown--}}
                    <li class="nav-item dropdown" id="append-new-admin-notification">
                        @include('backend.layouts.partials._notifications', ['notifications' => $authUser->getRecentNotifications()])
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                           data-coreui-toggle="tooltip"
                           data-coreui-title="@lang('Control Panel')"
                           href="{{ route('admin.app.control-panel') }}">
                            <x-icon name="apps-1" class="icon icon-lg"/>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                           data-coreui-toggle="tooltip"
                           data-coreui-title="Visit Landing Page"
                           href="{{ route('home') }}" target="_blank">
                            <x-icon name="cil-laptop" class="icon icon-lg"/>
                        </a>
                    </li>
                </ul>

                <span class="admin-header-divider" aria-hidden="true"></span>

                <ul class="header-nav admin-header-actions admin-header-actions--secondary">
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link d-inline-flex align-items-center justify-content-center"
                                type="button"
                                aria-expanded="false" data-coreui-toggle="dropdown"
                                data-coreui-toggle-tooltip="tooltip"
                                data-coreui-title="@lang('Language')"
                                aria-label="@lang('Language')">
                            <x-icon name="cil-language" class="icon icon-lg theme-icon-active"/>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end admin-header-dropdown admin-header-dropdown--lang">
                            @foreach($languages as $language)
                                <li>
                                    <a href="{{ route('locale-set', $language->code) }}"
                                       class="dropdown-item admin-header-lang-item d-flex align-items-center {{ $language->code == app()->getLocale() ? 'active' : '' }}">
                                        <img class="admin-header-lang-flag" src="{{ asset($language->flag) }}" width="22" height="16" alt="" loading="lazy">
                                        <span class="admin-header-lang-label">{{ $language->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>

                <div class="dropdown admin-header-profile">
                    <a class="nav-link admin-header-profile-trigger" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true"
                       aria-expanded="false"
                       aria-label="{{ $authName }} {{ __('account menu') }}">
                        <span class="admin-header-profile-avatar-wrap">
                            <span class="avatar avatar-md admin-header-profile-avatar">
                                <img class="avatar-img" src="{{ asset($authAvatar) }}" alt="{{ $authName }}" loading="lazy">
                            </span>
                            <span class="admin-header-profile-status" aria-hidden="true">
                                <span class="visually-hidden">{{ __('Online') }}</span>
                            </span>
                        </span>
                        <span class="admin-header-profile-meta d-none d-lg-flex">
                            <span class="admin-header-profile-name">{{ $authName }}</span>
                            <span class="admin-header-profile-role">{{ $authRoleText }}</span>
                        </span>
                        <span class="admin-header-profile-chevron d-none d-lg-inline-flex" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pt-0 admin-header-dropdown admin-header-profile-dropdown">
                        <div class="admin-header-profile-card">
                            <span class="avatar avatar-md admin-header-profile-card__avatar">
                                <img class="avatar-img" src="{{ asset($authAvatar) }}" alt="{{ $authName }}" loading="lazy">
                            </span>
                            <span class="admin-header-profile-card__body">
                                <span class="admin-header-profile-card__name">{{ $authName }}</span>
                                @if($authEmail)
                                    <span class="admin-header-profile-card__email">{{ $authEmail }}</span>
                                @endif
                                <span class="admin-header-profile-card__role">
                                    <span class="admin-header-profile-role-dot" aria-hidden="true"></span>
                                    {{ $authRoleText }}
                                </span>
                            </span>
                        </div>

                        <a class="dropdown-item admin-header-profile-action" href="{{ route('admin.profile.view') }}">
                            <span class="admin-header-profile-action__icon">
                                <x-icon name="user-cog-1" class="icon"/>
                            </span>
                            <span class="admin-header-profile-action__label">{{ __('Profile Settings') }}</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item admin-header-profile-action" href="{{ route('admin.lock') }}">
                            <span class="admin-header-profile-action__icon">
                                <x-icon name="cil-lock-locked" class="icon"/>
                            </span>
                            <span class="admin-header-profile-action__label">{{ __('Lock Account') }}</span>
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item admin-header-profile-action admin-header-profile-action--danger">
                                <span class="admin-header-profile-action__icon">
                                    <x-icon name="cil-account-logout" class="icon"/>
                                </span>
                                <span class="admin-header-profile-action__label">{{ __('Logout') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
