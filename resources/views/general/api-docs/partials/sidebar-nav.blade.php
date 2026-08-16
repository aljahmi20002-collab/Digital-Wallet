<aside class="api-sidebar" id="apiSidebar">
	<div class="sidebar-content">
		<!-- Sidebar Header -->
		<div class="sidebar-header">
			<div class="sidebar-brand">
				<i class="fas fa-code text-primary me-2"></i>
				<span class="api-sidebar-brand-text">@lang('API Reference')</span>
			</div>
		</div>
		
		<!-- Navigation Menu -->
		<nav class="sidebar-nav">
			<!-- Getting Started Section -->
			<div class="nav-section">
				<div class="nav-section-title">@lang('Getting Started')</div>
				<a href="#overview" class="sidebar-link active">
					<i class="fas fa-rocket"></i>
					@lang('Overview')
				</a>
				<a href="#authentication" class="sidebar-link">
					<i class="fas fa-key"></i>
					@lang('Authentication')
				</a>
				<a href="#quick-start" class="sidebar-link">
					<i class="fas fa-play"></i>
					@lang('Quick Start')
				</a>
				<a href="#merchant-config" class="sidebar-link">
					<i class="fas fa-gears"></i>
					@lang('Merchant Setup')
				</a>
				<a href="#currency-gateway-rules" class="sidebar-link">
					<i class="fas fa-sliders-h"></i>
					@lang('Currency & Gateways')
				</a>
			</div>
			
			<!-- API Endpoints Section -->
			<div class="nav-section">
				<div class="nav-section-title">@lang('API Endpoints')</div>
				<a href="#initiate-payment" class="sidebar-link">
					<i class="fas fa-credit-card"></i>
					@lang('Initiate Payment')
					<span class="method-badge method-post">@lang('POST')</span>
				</a>
				<a href="#verify-payment" class="sidebar-link">
					<i class="fas fa-check-circle"></i>
					@lang('Verify Payment')
					<span class="method-badge method-get">@lang('GET')</span>
				</a>
				<a href="#site-info" class="sidebar-link">
					<i class="fas fa-circle-info"></i>
					@lang('Site Info')
					<span class="method-badge method-get">@lang('GET')</span>
				</a>
				<a href="#webhooks" class="sidebar-link">
					<i class="fas fa-link"></i>
					@lang('Webhooks')
				</a>
			</div>
			
			<!-- Code Examples Section -->
			<div class="nav-section">
				<div class="nav-section-title">@lang('Integration')</div>
				<a href="#integration-examples" class="sidebar-link">
					<i class="fas fa-code"></i>
					@lang('Perform Examples')
				</a>
				<a href="#woocommerce-integration" class="sidebar-link">
					<i class="fa-brands fa-wordpress text-info"></i>
					@lang('WooCommerce')
				</a>
				<a href="#testing" class="sidebar-link">
					<i class="fas fa-flask"></i>
					{{ __('Testing Guide') }}
				</a>
				<a href="#sandbox-guide" class="sidebar-link">
					<i class="fas fa-list-check"></i>
					{{ __('Go Live Checklist') }}
				</a>
			</div>
			
			<!-- Support Section -->
			<div class="nav-section mb-0">
				<div class="nav-section-title">@lang('Support')</div>
				<a href="#error-codes" class="sidebar-link">
					<i class="fas fa-exclamation-triangle"></i>
					@lang('Error Codes')
				</a>
				<a href="#support" class="sidebar-link">
					<i class="fas fa-life-ring"></i>
					@lang('Support')
				</a>
			</div>
		</nav>
	
	</div>
</aside>
<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
