@props([
    'title',
    'subtitle' => null,
    'icon' => 'fas fa-layer-group',
    'compact' => false,
])

@php
    $hasActions = trim((string) $slot) !== '';
@endphp

<div {{ $attributes->class(['card-title user-feature-header', 'user-feature-header--compact' => $compact]) }}>
    <div class="user-feature-header__main">
        <span class="user-feature-header__icon" aria-hidden="true">
            <i class="{{ $icon }}"></i>
        </span>

        <div class="user-feature-header__copy">
            <h6 class="user-feature-header__title">{{ $title }}</h6>

            @if($subtitle)
                <p class="user-feature-header__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if($hasActions)
        <div class="user-feature-header__actions">
            {{ $slot }}
        </div>
    @endif
</div>
