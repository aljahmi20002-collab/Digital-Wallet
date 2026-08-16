@extends('backend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/p2p-admin.css?v=' . config('app.version')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('backend/css/p2p-redesign.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    {{-- P2P Admin Shell: compact module header, tab navigation, and page content slot --}}
    @php
        $p2pMenu = getAdminMenuByCode('p2p-management');
        $visibleP2pMenus = collect($p2pMenu['sub_menus'] ?? [])
            ->filter(fn (array $menu): bool => empty($menu['permission']) || (bool) auth()->user()?->can($menu['permission']))
            ->values();
        $p2pIcon = trim($__env->yieldContent('p2p_icon', 'p2p_trading')) ?: 'p2p_trading';
    @endphp

    <div class="p2p-admin">
    <div class="py-4">
        {{-- Module Navigation + Page Content --}}
        <div class="card border-0 p2p-shell px-3 py-4">
            <div class="p2p-admin-topbar">
                <div class="p2p-admin-topbar__title">
                    <span class="pa-title-icon">
                        <x-icon :name="$p2pIcon" height="22" width="22"/>
                    </span>
                    <h1>@yield('p2p_title')</h1>
                </div>

                @hasSection('p2p_action')
                    <div class="p2p-admin-topbar__actions">
                        @yield('p2p_action')
                    </div>
                @endif
            </div>

            @if($visibleP2pMenus->isNotEmpty())
                <div class="p2p-nav-wrap">
                    <ul class="nav nav-pills bg-light p2p-nav rounded p-1">
                        @foreach($visibleP2pMenus as $menu)
                            <li class="nav-item">
                                <a class="nav-link {{ isActive($menu['route'], $menu['params'] ?? []) }}" aria-current="page"
                                   href="{{ route($menu['route'], $menu['params'] ?? []) }}">
                                    <x-icon name="{{ $menu['icon'] }}" height="18" width="18"/>
                                    {{ title($menu['label']) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p2p-body py-3">
                @yield('p2p_content')
            </div>
        </div>
    </div>
    </div>
@endsection
