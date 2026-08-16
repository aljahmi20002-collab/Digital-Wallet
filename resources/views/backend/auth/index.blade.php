<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar','he','fa','ur']) ? 'rtl' : 'ltr' }}">
@include('backend.layouts.partials._head')
<body class="backend-admin-layout backend-auth-layout">

{{-- Demo Mode Banner (renders only when APP_DEMO=true) --}}
<x-demo-banner/>

<main role="main" class="admin-auth-page min-vh-100">
	<div class="container admin-auth-container">
		<div class="row justify-content-center">
			<div class="col-12 col-xl-11">
				<div class="admin-auth-shell">
					<div class="admin-auth-showcase d-none d-lg-flex"
					     style="background-image: linear-gradient(180deg, rgba(15, 23, 42, 0.2), rgba(15, 23, 42, 0.68)), url('{{ asset(setting('login_banner')) }}');">
						<div class="admin-auth-showcase__content">
							<span class="admin-auth-showcase__badge">{{ __('Admin Access') }}</span>
							<div class="admin-auth-showcase__stats">
								<div class="admin-auth-showcase__stat">
                                    <span class="admin-auth-showcase__stat-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                    </span>
									<div class="admin-auth-showcase__stat-content">
										<span class="admin-auth-showcase__stat-value">{{ __('Secure') }}</span>
										<span class="admin-auth-showcase__stat-label">{{ __('Protected admin entry') }}</span>
									</div>
								</div>
								<div class="admin-auth-showcase__stat">
                                    <span class="admin-auth-showcase__stat-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                    </span>
									<div class="admin-auth-showcase__stat-content">
										<span class="admin-auth-showcase__stat-value">{{ __('Fast') }}</span>
										<span class="admin-auth-showcase__stat-label">{{ __('Focused authentication flow') }}</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="admin-auth-panel">
						<div class="admin-auth-panel__inner">
							<a href="{{ route('admin.login-view') }}" class="admin-auth-brand" aria-label="{{ setting('site_title', config('app.name')) }}">
								<img src="{{ asset(setting('logo')) }}" class="admin-auth-brand__logo" alt="{{ setting('site_title') }}" loading="lazy" decoding="async">
							</a>
							@yield('auth-content')
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

@include('backend.layouts.partials._scripts')
</body>
</html>
