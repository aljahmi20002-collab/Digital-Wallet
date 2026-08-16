@props(['href'])

<a href="{{ $href }}" {{ $attributes->class('dashboard-sidebar-referral-card') }}>
    <span class="dashboard-sidebar-referral-card__image" aria-hidden="true">
        <x-icon name="gift" width="76" height="58" class="dashboard-sidebar-referral-card__gift"/>
        <span class="dashboard-sidebar-referral-card__person">
            <x-icon name="user" width="16" height="16" class="dashboard-sidebar-referral-card__person-icon"/>
        </span>
    </span>
    <span class="dashboard-sidebar-referral-card__body">
        <span class="dashboard-sidebar-referral-card__badge">
            <x-icon name="arrow-up-right" width="14" height="14" class="dashboard-sidebar-referral-card__badge-icon"/>
            {{ __('Referral Boost') }}
        </span>
        <span class="dashboard-sidebar-referral-card__title">{{ __('Invite friends & earn') }}</span>
        <span class="dashboard-sidebar-referral-card__subtitle">{{ __('Share your link and earn rewards') }}</span>
    </span>
    <span class="dashboard-sidebar-referral-card__action" aria-hidden="true">
        <x-icon name="arrow-right" width="16" height="16" class="dashboard-sidebar-referral-card__arrow"/>
    </span>
</a>
