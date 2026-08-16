@extends('backend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/subscription-admin.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    @php
        $subMenu    = getAdminMenuByCode('subscription-management');
        $visibleMenus = collect($subMenu['sub_menus'] ?? [])
            ->filter(fn (array $menu): bool => empty($menu['permission']) || (bool) auth()->user()?->can($menu['permission']))
            ->values();
        $pageIcon = trim($__env->yieldContent('sub_icon', 'layer')) ?: 'layer';
    @endphp

    <div class="sub-admin">
        <div class="sa-admin-header py-4">
            <div>
                <h1 class="sa-admin-header__title mb-1">
                    <span class="sa-admin-header__icon">
                        <x-icon :name="$pageIcon" height="24" width="24"/>
                    </span>
                    <span>@yield('sub_title')</span>
                </h1>
                <div class="text-muted small">@yield('sub_subtitle')</div>
            </div>

            @hasSection('sub_action')
                <div class="sa-admin-header__actions">
                    @yield('sub_action')
                </div>
            @endif
        </div>

        <div class="card border-0 sa-admin-shell px-3 py-4">
            @if($visibleMenus->isNotEmpty())
                <div class="we-admin-nav-wrap mb-3">
                    <ul class="nav nav-pills bg-light sa-admin-nav rounded p-1">
                        @foreach($visibleMenus as $menu)
                            <li class="nav-item">
                                <a class="nav-link {{ isActive($menu['route'], $menu['params'] ?? []) }}"
                                   href="{{ route($menu['route'], $menu['params'] ?? []) }}">
                                    <x-icon name="{{ $menu['icon'] }}" height="18" width="18"/>
                                    {{ title($menu['label']) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="sa-admin-body">
                @yield('sub_content')
            </div>
        </div>
    </div>
@endsection
