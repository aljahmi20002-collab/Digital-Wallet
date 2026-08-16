@php
	$sidebarIcon ??= static function (?string $icon): string {
		$iconMap = config('admin_menus.sidebar_icons.map', []);
		$defaultIcon = config('admin_menus.sidebar_icons.default', 'sidebar-subitem');

		if (blank($icon)) {
			return $defaultIcon;
		}

		return $iconMap[$icon] ?? $defaultIcon;
	};
	$sidebarQuickLinkIconSize = config('admin_menus.sidebar_icons.quick_link_size', 18);
	$adminPermissions = $adminPermissions ?? session('admin_permissions', []);
	$sidebarQuickLinks = collect(config('admin_menus.sidebar_footer', []))->filter(function (array $link) use ($adminPermissions) {
		$permission = $link['permission'] ?? null;

		return is_null($permission) || in_array($permission, $adminPermissions, true);
	})->values();
@endphp
@if($sidebarQuickLinks->isNotEmpty())
	<div class="sidebar-shortcut-panel sidebar-footer-quick-links">
		<div class="sidebar-quick-links" role="navigation" aria-label="{{ __('Quick links') }}">
			@foreach($sidebarQuickLinks as $quickLink)
				@php
					$isActive = trim(isActive($quickLink['route'])) === 'active';
					$quickLinkTitle = $quickLink['title'] ?? $quickLink['label'];
				@endphp
				<a href="{{ route($quickLink['route']) }}"
				   @class([
					   'sidebar-quick-link',
					   'active' => $isActive,
				   ])
				   aria-label="{{ __($quickLinkTitle) }}"
				   @if($isActive) aria-current="page" @endif
				   data-accent="{{ $quickLink['accent'] ?? 'primary' }}"
				   data-coreui-toggle="tooltip"
				   data-coreui-placement="top"
				   data-coreui-title="{{ __($quickLinkTitle) }}">
					<span class="sidebar-quick-link__surface" aria-hidden="true">
						<x-icon name="{{ isset($sidebarIcon) ? $sidebarIcon($quickLink['icon'] ?? null) : ($quickLink['icon'] ?? 'app') }}" :width="$sidebarQuickLinkIconSize" :height="$sidebarQuickLinkIconSize" class="sidebar-quick-link__icon"/>
					</span>
				</a>
			@endforeach
		</div>
	</div>
@endif
