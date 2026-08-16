@extends('backend.layouts.app')
@section('title', __('Control Panel'))

@section('content')
    @php
        $totalSections = count($controlPanelData ?? []);
        $totalFeatures = !empty($controlPanelData) ? count(array_merge(...array_column($controlPanelData, 'features'))) : 0;
    @endphp

    <!-- Header Section -->
    <div class="control-panel-shell">
        <div class="control-panel-header mb-4">
            <div class="control-panel-header-top">
                <div class="header-content">
                    <div class="d-flex align-items-center">
                        <div class="header-icon">
                            <x-icon name="apps-1" class="icon"/>
                        </div>
                        <div class="ms-3">
                            <span class="header-kicker">@lang('Admin Workspace')</span>
                            <h1 class="header-title mb-0">@lang('Control Panel')</h1>
                            <p class="header-subtitle mb-0">@lang('Curated access to priority administrative modules')</p>
                        </div>
                    </div>
                </div>
                <div class="header-overview">
                    <div class="header-stat-card header-stat-card--primary">
                        <span class="stat-number" id="total-features">{{ $totalFeatures }}</span>
                        <span class="stat-label">@lang('Features')</span>
                    </div>
                    <div class="header-stat-card">
                        <span class="stat-number">{{ $totalSections }}</span>
                        <span class="stat-label">@lang('Sections')</span>
                    </div>
                </div>
            </div>

            <div class="control-panel-toolbar mt-3">
                <div class="control-panel-search">
                    <div class="search-wrapper">
                        <div class="search-icon">
                            <x-icon name="search" class="icon"/>
                        </div>
                        <input type="text" id="control-panel-search" class="search-input" placeholder="@lang('Search features')..." autocomplete="off">
                        <div class="search-clear d-none" id="search-clear">
                            <x-icon name="x" class="icon"/>
                        </div>
                    </div>
                </div>

                <div class="control-panel-meta">
                    <span class="control-panel-meta-item">
                        <span class="meta-dot"></span>
                        @lang('Curated modules')
                    </span>
                    <span class="control-panel-meta-item">
                        <span class="meta-dot"></span>
                        @lang('Faster navigation')
                    </span>
                </div>
            </div>
        </div>

        <!-- Control Panel Grid -->
        <div class="control-panel-grid">
            @foreach($controlPanelData as $sectionIndex => $section)
                <div class="section-block" data-section="{{ $sectionIndex }}" data-section-code="{{ $section['code'] }}">
                    <div class="section-header">
                        <div class="section-heading-group">
                            <div class="section-icon-wrap">
                                <div class="section-indicator"></div>
                                <div class="section-icon bg-{{ $section['color'] }}">
                                    <x-icon name="{{ $section['icon'] }}" class="text-white" height="18" width="18"/>
                                </div>
                            </div>
                            <div>
                                <h2 class="section-title">{{ __($section['label']) }}</h2>
                                <p class="section-subtitle mb-0">{{ __($section['description']) }}</p>
                            </div>
                        </div>
                        <span class="section-count">{{ count($section['features']) }}</span>
                    </div>

                    <div class="features-grid">
                        @foreach($section['features'] as $featureIndex => $feature)
                            <a href="{{ $feature['url'] }}" class="feature-card" data-color="{{ $feature['color'] }}" data-feature="{{ $featureIndex }}" data-feature-code="{{ $feature['code'] }}" data-keywords="{{ strtolower(implode(' ', $feature['keywords'] ?? [])) }}">
                                <div class="feature-content">
                                    <div class="feature-header">
                                        <div class="feature-main">
                                            <div class="feature-icon-wrapper">
                                                <div class="feature-icon bg-{{ $feature['color'] }}">
                                                    <x-icon name="{{ $feature['icon'] }}" class="text-white" height="20" width="20"/>
                                                </div>
                                            </div>
                                            <div class="feature-body">
                                                <div class="feature-title-row">
                                                    <h3 class="feature-title">{{ __($feature['label']) }}</h3>
                                                    @if(!empty($feature['badge']))
                                                        <span class="feature-parent">{{ __($feature['badge']) }}</span>
                                                    @endif
                                                </div>
                                                <p class="feature-description">{{ __($feature['description']) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="feature-footer">
                                        <span class="access-hint">@lang('Open module')</span>
                                        <div class="access-arrow">
                                            <x-icon name="arrow-right"/>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if(empty($controlPanelData))
                <div class="empty-state">
                    <div class="empty-icon">
                        <x-icon name="apps-1" class="icon"/>
                    </div>
                    <h3 class="empty-title">@lang('No features available')</h3>
                    <p class="empty-description">@lang('Contact your administrator for access permissions.')</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@push('scripts')
    @include('backend.app.partials._control_panel_scripts')
@endpush
