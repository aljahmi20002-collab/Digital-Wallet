@php
	// Theme is resolved at render time. The page builder already saves each
	// component with its theme tag, but we still gate on activeTheme() so a
	// freshly-switched theme renders only its own blocks on the next request.
	$gdkActiveTheme = activeTheme();
	$gdkThemeSlug   = $gdkActiveTheme->value;

	$gdkLayoutView      = 'frontend.layouts.'.$gdkThemeSlug.'.app';
	$gdkSectionPathBase = 'frontend.pages.sections.'.$gdkThemeSlug;

	// Fall back to the classic layout/sections if the active theme's files
	// aren't present yet (defensive: e.g. mid-deploy of a new theme).
	if (! view()->exists($gdkLayoutView)) {
		$gdkLayoutView      = 'frontend.layouts.app';
		$gdkSectionPathBase = 'frontend.pages.sections';
	}
@endphp

@extends($gdkLayoutView)

@section('content')
	@if($isBreadcrumb)
		@include('frontend.pages.partials._breadcrumb')
	@endif

	@foreach($components as $component)
		@php
			$gdkSectionView = $gdkSectionPathBase.'._'.$component->section_name;
			// Component-level fallback: if the active theme is missing a
			// particular section view, use the classic one for that block.
			if (! view()->exists($gdkSectionView)) {
				$gdkSectionView = 'frontend.pages.sections._'.$component->section_name;
			}
		@endphp

		<div class="@isset($isPage) page-section @endisset">
			@include($gdkSectionView, [
				'data'             => $component->content_data,
				'repeatedContents' => $component->repeatedContents,
				'component'        => $component,
			])
		</div>
	@endforeach
@endsection
