@props([
    'title' => __('Nothing found'),
    'message' => __('There is nothing to show here yet.'),
    'eyebrow' => null,
    'icon' => 'fa-search',
    'actionUrl' => null,
    'actionLabel' => null,
    'actionIcon' => 'fa-plus',
    'secureLabel' => null,
])

@php($hasPreview = isset($preview) && $preview->hasActualContent())

<div {{ $attributes->class(['user-not-found payment-link-empty', 'payment-link-empty--solo' => ! $hasPreview]) }}>
    <div class="user-not-found__content payment-link-empty__content">
        <div class="user-not-found__visual payment-link-empty__visual" aria-hidden="true">
            <span class="user-not-found__orb user-not-found__orb--one payment-link-empty__orb payment-link-empty__orb--one"></span>
            <span class="user-not-found__orb user-not-found__orb--two payment-link-empty__orb payment-link-empty__orb--two"></span>
            <div class="user-not-found__icon payment-link-empty__icon">
                <i class="fas {{ $icon }}"></i>
            </div>
        </div>

        @if($eyebrow)
            <span class="user-not-found__eyebrow payment-link-empty__eyebrow">
                <i class="fas fa-bolt" aria-hidden="true"></i>
                {{ $eyebrow }}
            </span>
        @endif

        <h6 class="user-not-found__title payment-link-empty__title">{{ $title }}</h6>
        <p class="user-not-found__hint payment-link-empty__hint">{{ $message }}</p>

        @if($actionUrl || $secureLabel)
            <div class="user-not-found__actions payment-link-empty__actions">
                @if($actionUrl && $actionLabel)
                    <a href="{{ $actionUrl }}" class="btn btn-primary user-not-found__primary payment-link-empty__primary">
                        <i class="fas {{ $actionIcon }}" aria-hidden="true"></i>
                        {{ $actionLabel }}
                    </a>
                @endif

                @if($secureLabel)
                    <span class="user-not-found__secure payment-link-empty__secure">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        {{ $secureLabel }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    @if($hasPreview)
        <div class="user-not-found__preview payment-link-empty__preview" aria-hidden="true">
            {{ $preview }}
        </div>
    @endif
</div>
