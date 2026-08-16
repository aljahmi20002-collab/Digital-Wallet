@php
	use App\Support\UserNavigationBreadcrumbs;

	$authUser = auth()->user();
	$roleTitle = $authUser->role->title();
	$userNavigationBreadcrumbs = UserNavigationBreadcrumbs::forRoute(request()->route()?->getName());
@endphp

<header class="navbar-area premium-header d-lg-block d-none">
	<div class="ph-inner">
		
		{{-- Brand --}}
		@php
			$siteTitle = setting('site_title');
			$brandLead = mb_substr($siteTitle, 0, max(1, mb_strlen($siteTitle) - 4));
			$brandTail = mb_substr($siteTitle, max(1, mb_strlen($siteTitle) - 4));
		@endphp
		<a href="{{ route('home') }}" class="ph-brand" aria-label="{{ $siteTitle }}">
                <span class="ph-brand__mark" aria-hidden="true">
                   <img src="{{ asset(setting('small_logo')) }}" alt="{{$siteTitle}}" loading="lazy">
                 </span>
			<span class="ph-brand__text">
                <span class="ph-brand__name">{{ $brandLead }}<span>{{ $brandTail }}</span></span>
                <span class="ph-brand__tag">{{ __(':role · Wallet', ['role' => $roleTitle]) }}</span>
            </span>
		</a>
		
		{{-- Center: page title + breadcrumb --}}
		<div class="ph-center">
			<div class="ph-page-title-block">
				<div class="ph-breadcrumb">
					<span>{{ __(':role Dashboard', ['role' => $roleTitle]) }}</span>
					@forelse($userNavigationBreadcrumbs as $breadcrumb)
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polyline points="9 18 15 12 9 6"></polyline>
						</svg>
						<span @class(['current' => $loop->last])>{{ __($breadcrumb) }}</span>
					@empty
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polyline points="9 18 15 12 9 6"></polyline>
						</svg>
						<span class="current">@yield('title', __('Overview'))</span>
					@endforelse
				</div>
				<h1 class="ph-page-title">
					@yield('title', __(':role Dashboard', ['role' => $roleTitle]))
				</h1>
			</div>
		</div>
		
		{{-- Right cluster --}}
		<div class="ph-actions">
			
			{{-- My QR Code --}}
			<a href="{{ route('user.wallet.my-qr-code') }}"
			   class="ph-icon-btn ph-icon-btn--primary"
			   title="{{ __('My QR Code') }}"
			   aria-label="{{ __('My QR Code') }}">
				<i class="fas fa-qrcode"></i>
			</a>
			
			{{-- Language --}}
			<div class="ph-lang">
				@include('frontend.layouts.user.partials._language_switcher')
			</div>
			
			<div class="ph-divider" aria-hidden="true"></div>
			
			{{-- Notifications --}}
			<div class="ph-notify append-new-notification">
				@include('frontend.layouts.user.partials._notifications')
			</div>
			
			{{-- Quick functions --}}
			<div class="ph-quick-wrap">
				<button type="button"
				        class="ph-quick"
				        id="quickFunctionBtnDesktop"
				        title="{{ __('Quick Functions') }}"
				        aria-label="{{ __('Quick Functions') }}">
					<x-icon name="apps" height="18" width="18"/>
				</button>
				@include('frontend.layouts.user.partials._quick_functions', [
					'dropdownId' => 'quickFunctionDropdownDesktop',
					'btnId' => 'quickFunctionBtnDesktop',
				])
			</div>
			
			{{-- Profile --}}
			@include('frontend.layouts.user.partials._author_card')
		
		</div>
	</div>
</header>
