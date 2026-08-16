@php
    $resolvedIcon = $component->resolved_component_icon;
    $wrapperClass = trim('component-admin-thumb ' . ($wrapperClass ?? ''));
    $imageClass = trim('component-admin-thumb__img ' . ($imageClass ?? ''));
@endphp

<span class="{{ $wrapperClass }}">
    @if($resolvedIcon)
        <img src="{{ asset($resolvedIcon) }}" alt="{{ $component->component_name }}" class="{{ $imageClass }}" loading="lazy">
    @else
        <span class="component-admin-thumb__fallback" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 14.5L10.7 11.8C11.1 11.4 11.7 11.4 12.1 11.8L13.8 13.5L15 12.3C15.4 11.9 16 11.9 16.4 12.3L18 13.9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="9" cy="9" r="1.25" fill="currentColor"/>
            </svg>
        </span>
    @endif
</span>
