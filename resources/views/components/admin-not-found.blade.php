@props([
    'title' => __('No records found'),
    'message' => __('Try changing filters or create a new record to get started.'),
    'icon' => 'fa-search',
    'actionUrl' => null,
    'actionLabel' => null,
    'actionIcon' => 'fa-plus',
])

<div {{ $attributes->class(['admin-not-found']) }}>
    <div class="admin-not-found__visual" aria-hidden="true">
        <span class="admin-not-found__ring"></span>
        <span class="admin-not-found__icon">
            <i class="fa-solid {{ $icon }}"></i>
        </span>
    </div>

    <div class="admin-not-found__content">
        <h6 class="admin-not-found__title">{{ $title }}</h6>
        <p class="admin-not-found__message">{{ $message }}</p>
    </div>

    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm admin-not-found__action">
            <i class="fa-solid {{ $actionIcon }}" aria-hidden="true"></i>
            {{ $actionLabel }}
        </a>
    @endif
</div>
