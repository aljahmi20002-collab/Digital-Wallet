<!DOCTYPE html>
    <html lang="{{ app()->getLocale() }}">

    {{-- Push welcome-bonus CSS BEFORE the head is rendered so it lands inside <head>. --}}
    @if(! empty($signupBonusPopup))
        @push('styles')
            <link rel="stylesheet" href="{{ asset('assets/frontend/css/signup-bonus.css?v=' . filemtime(public_path('assets/frontend/css/signup-bonus.css'))) }}">
        @endpush
    @endif

    {{-- Head Include Here --}}
    @include('frontend.layouts.user.partials._head')
    
    <body @class([
        'dashboard-user-layout',
        'dashboard-role-agent' => auth()->user()?->isAgent(),
        'dashboard-role-merchant' => auth()->user()?->isMerchant(),
        'dashboard-role-user' => auth()->user() && ! auth()->user()->isAgent() && ! auth()->user()->isMerchant(),
        'dashboard-home-route' => request()->routeIs('user.dashboard'),
    ])>

    {{-- Demo Mode Banner (renders only when APP_DEMO=true) --}}
    <x-demo-banner />

    {{-- Header Include Here --}}
    @include('frontend.layouts.user.partials._navbar')
    
    {{-- Mobile Navbar Include Here --}}
    @include('frontend.layouts.user.partials._mobile_navbar')
    @include('frontend.layouts.user.partials._mobile_app_header')
    
    
    
    {{-- Main Area Here --}}
    <div id="mainArea" class="main-area mb-30 dashboard-main-area" data-dashboard-scroll-root>
        <div class="container">
            <div class="row wrapper fixed-wrapper">
                {{-- left bar --}}
                <div class="col-xl-3 col-lg-4 sidebar  @if( auth()->user() && auth()->user()->isMerchant()) isMercent @endif">
                    <div class="dashboard-sidebar-layout">
                        {{-- left bar wallet card --}}
                        <div class="dashboard-sidebar-wallet">
                            @include('frontend.user.dashboard.partials._left_bar_card')
                        </div>

                        {{-- left bar menu --}}
                        <div class="dashboard-sidebar-menu-scroll" data-dashboard-sidebar-scroll>
                            @include('frontend.user.dashboard.partials._left_bar_menu_grouped')
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8  main-content dashboard-main-content @if(!request()->routeIs('user.dashboard')) mt-neg-120 @endif" data-dashboard-main-scroll>
                    {{-- content area --}}
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @include('frontend.user.wallet.partials._add_wallet_modal')

    {{-- Footer Mobile Include here --}}
    @include('frontend.layouts.user.partials._footer_mobile')
    @include('frontend.layouts.user.partials._mobile_app_footer')

    {{-- Welcome bonus popup (renders only when conditions are met by ViewComposer) --}}
    @if(! empty($signupBonusPopup))
        @include('frontend.partials.signup-bonus-popup')
        @push('scripts')
            <script src="{{ asset('assets/frontend/js/signup-bonus.js?v=' . filemtime(public_path('assets/frontend/js/signup-bonus.js'))) }}"></script>
        @endpush
    @endif

    {{-- Scripts Include here --}}
    @include('frontend.layouts.user.partials._script')

    </body>
</html>
