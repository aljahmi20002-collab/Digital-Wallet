@php
	use App\Services\FeatureManager;

	$sidebarIconMap = config('admin_menus.sidebar_icons.map', []);
	$sidebarIconDefault = config('admin_menus.sidebar_icons.default', 'sidebar-subitem');
	$sidebarMenuIconSize = config('admin_menus.sidebar_icons.menu_size', 22);
	$sidebarSubmenuIconSize = config('admin_menus.sidebar_icons.submenu_size', 19);
	$adminMenuSectionOrder = [
		'' => 0,
		'Account Management' => 10,
		'Finance & Wallet' => 20,
		'System Config' => 30,
		'Communication Center' => 40,
		'Staff Management' => 50,
		'Content Management' => 60,
	];
	$adminMenuSections = collect(config('admin_menus'))
		->filter(fn ($section) => is_array($section) && isset($section['menus']))
		->sortBy(fn (array $section): int => $adminMenuSectionOrder[$section['label'] ?? ''] ?? 100);
	$adminPermissions = session('admin_permissions', []);
	$featureManager = app(FeatureManager::class);

	$sidebarIcon = static function (?string $icon) use ($sidebarIconMap, $sidebarIconDefault): string {
		if (blank($icon)) {
			return $sidebarIconDefault;
		}

		return $sidebarIconMap[$icon] ?? $sidebarIconDefault;
	};
@endphp
<div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
	{{-- Sidebar Header --}}
	<div class="sidebar-header border-bottom">
		<div class="sidebar-brand">
			<img class="sidebar-brand-full" height="32" src="{{ asset(setting('light_logo')) }}" alt="Logo" loading="lazy">
			<img class="sidebar-brand-narrow" width="32" height="32" src="{{ asset(setting('small_logo')) }}" alt="Small Logo" loading="lazy">
		</div>
		<button class="btn-close d-lg-none" type="button"
		        data-coreui-dismiss="offcanvas" data-coreui-theme="dark"
		        aria-label="Close"
		        onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
		</button>
	</div>
	
	{{-- Sidebar Navigation --}}
	<ul class="sidebar-nav overflow-auto" data-coreui="navigation">
		@foreach($adminMenuSections as $section)
			@php
				$visibleMenus = collect($section['menus'] ?? [])->filter(function ($menu) use ($adminPermissions, $featureManager) {
					$permission = $menu['permission'] ?? null;
					$hasPermission = is_null($permission) || in_array($permission, $adminPermissions, true);

					if (isset($menu['feature_key']) && !$featureManager->isEnabled($menu['feature_key'])) {
						return false;
					}
	
					if ($menu['type'] === 'groups') {
						$hasVisibleSub = collect($menu['sub_menus'] ?? [])->filter(function ($sub) use ($adminPermissions, $featureManager) {
							$subPerm = $sub['permission'] ?? $sub['can'] ?? null;
							$hasSubPermission = is_null($subPerm) || in_array($subPerm, $adminPermissions, true);
							$hasSubFeature = !isset($sub['feature_key']) || $featureManager->isEnabled($sub['feature_key']);

							return $hasSubPermission && $hasSubFeature;
						})->isNotEmpty();
						return $hasPermission && $hasVisibleSub;
					}
	
					return $hasPermission;
				});
			@endphp
			
			@if($visibleMenus->isNotEmpty())
				@if(!empty($section['label']))
					<li class="nav-title">{{ __($section['label']) }}</li>
				@endif
				
				
				@foreach($visibleMenus as $menu)
					@php
						$menuGroupKey = (string) str($menu['code'] ?? $menu['route'] ?? $menu['label'] ?? '')->slug('-');
						$menuIndicator = data_get($sidebarIndicators ?? [], 'groups.'.$menuGroupKey);
					@endphp
					
					@if($menu['type'] === 'single')
						<li class="nav-item">
							<a class="nav-link {{ isActive($menu['route']) }}" href="{{ route($menu['route']) }}">
								<x-icon name="{{ $sidebarIcon($menu['icon'] ?? null) }}" :width="$sidebarMenuIconSize" :height="$sidebarMenuIconSize" class="nav-icon"/>
								<span class="sidebar-link-text nav-label">{{ __($menu['label']) }}</span>
								@if($menuIndicator)
									<span class="sidebar-indicator sidebar-indicator--menu sidebar-indicator--{{ $menuIndicator['tone'] }} ms-auto"
									      title="{{ __('Pending items: :count', ['count' => $menuIndicator['count']]) }}">
                                        <span class="sidebar-indicator__dot"></span>
                                        <span class="sidebar-indicator__count">{{ $menuIndicator['display'] }}</span>
                                    </span>
								@endif
							</a>
						</li>
					@elseif($menu['type'] === 'groups')
						<li class="nav-group {{ isActive(array_column($menu['sub_menus'], 'route'), null, 'show') }}">
							<a class="nav-link nav-group-toggle" href="#">
								<x-icon name="{{ $sidebarIcon($menu['icon'] ?? null) }}" :width="$sidebarMenuIconSize" :height="$sidebarMenuIconSize" class="nav-icon"/>
								<span class="sidebar-link-text nav-label">{{ __($menu['label']) }}</span>
								@if($menuIndicator)
									<span class="sidebar-indicator sidebar-indicator--menu sidebar-indicator--{{ $menuIndicator['tone'] }} ms-auto"
									      title="{{ __('Pending items: :count', ['count' => $menuIndicator['count']]) }}">
                                        <span class="sidebar-indicator__dot"></span>
                                        <span class="sidebar-indicator__count">{{ $menuIndicator['display'] }}</span>
                                    </span>
								@endif
							</a>
							<ul class="nav-group-items compact">
								@foreach($menu['sub_menus'] as $sub)
									@php
										$subPermission = $sub['permission'] ?? $sub['can'] ?? null;
										$hasSubPermission = is_null($subPermission) || in_array($subPermission, $adminPermissions, true);
										$hasSubFeature = !isset($sub['feature_key']) || $featureManager->isEnabled($sub['feature_key']);
										$subIndicator = data_get($sidebarIndicators ?? [], ['routes', $sub['route']]);
									@endphp
									
									@if($hasSubPermission && $hasSubFeature)
										<li class="nav-item">
											<a class="nav-link {{ isActive($sub['route'], $sub['params'] ?? []) }}"
											   href="{{ route($sub['route'], $sub['params'] ?? []) }}">
                                            <span class="nav-icon">
                                                <x-icon name="{{ $sidebarIcon($sub['icon'] ?? null) }}" :width="$sidebarSubmenuIconSize" :height="$sidebarSubmenuIconSize" class="icon"/>
                                            </span>
												<span class="sidebar-submenu-content">
                                                    <span class="sidebar-link-text sidebar-link-text--submenu nav-label">{{ __($sub['label']) }}</span>
                                                    
                                                    @if($subIndicator)
														<span class="sidebar-indicator sidebar-indicator--submenu sidebar-indicator--{{ $subIndicator['tone'] }}"
														      title="{{ __('Pending items: :count', ['count' => $subIndicator['count']]) }}">
                                                            <span class="sidebar-indicator__dot"></span>
                                                            <span class="sidebar-indicator__count">{{ $subIndicator['display'] }}</span>
                                                        </span>
													@endif
                                                </span>
											</a>
										</li>
									@endif
								@endforeach
							</ul>
						</li>
					@endif
				@endforeach
			@endif
		@endforeach
	</ul>
	
	{{-- Sidebar Footer Shortcuts --}}
	@include('backend.layouts.partials._sidebar_footer')
</div>
