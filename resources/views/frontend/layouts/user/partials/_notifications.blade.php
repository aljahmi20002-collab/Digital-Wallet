@php
    use App\Enums\NotificationActionType;

    $notificationCount = $notifications->count();
    $visibleNotifications = $notifications->take(9);
    $hasNotifications = $notificationCount > 0;
    $countDisplay = $notificationCount > 9 ? '9+' : (string) $notificationCount;
@endphp

<div class="notice user-notification-trigger {{ $hasNotifications ? 'has-unread' : '' }}"
     id="userNotificationTrigger"
     role="button"
     data-bs-toggle="dropdown"
     aria-haspopup="true"
     aria-expanded="false"
     aria-label="{{ trans_choice('{0} No new notifications|{1} :count new notification|[2,*] :count new notifications', $notificationCount, ['count' => $notificationCount]) }}">
    {{-- Mobile View: Show Different Icons Based on Notification Count --}}
    <div class="user-notification-trigger__icon user-notification-trigger__icon--mobile d-lg-none d-grid">
        <x-icon name="{{ $notificationCount > 0 ? 'notice' : 'bell' }}" height="25" width="25" class="text-white"/>
    </div>

    {{-- Desktop View: Always Show Bell Icon with Notification Badge --}}
    <div class="user-notification-trigger__icon user-notification-trigger__icon--desktop d-lg-grid d-none">
        <x-icon name="bell" height="20" width="20"/>
    </div>
    @if($notificationCount > 0)
        <span class="user-notification-trigger__badge" aria-hidden="true">{{ $countDisplay }}</span>
        <span class="visually-hidden">{{ trans_choice(':count new notification|:count new notifications', $notificationCount, ['count' => $notificationCount]) }}</span>
    @endif
</div>

{{-- Notification Dropdown Menu --}}
<div class="dropdown-menu dropdown-menu-end single-card-box notification-dropdown user-notification-dropdown user-notification-dropdown--premium"
     data-bs-display="static"
     role="menu"
     aria-labelledby="userNotificationTrigger">
    <div class="notification-dropdown__header user-notification-dropdown__header">
        <span class="user-notification-dropdown__head-icon" aria-hidden="true">
            <x-icon name="wallet-1" height="20" width="20"/>
        </span>
        <div class="user-notification-dropdown__heading">
            <span class="notification-dropdown__eyebrow user-notification-dropdown__eyebrow">{{ __('Wallet Alerts') }}</span>
            <h6 class="notification-dropdown__title user-notification-dropdown__title">{{ __('Notifications') }}</h6>
            <span class="notification-dropdown__subtitle user-notification-dropdown__subtitle">{{ __('Wallet activity updates') }}</span>
        </div>
        <span class="notification-dropdown__count user-notification-dropdown__count {{ $hasNotifications ? 'is-live' : 'is-clear' }}">
            @if($hasNotifications)
                <span class="user-notification-dropdown__status-dot" aria-hidden="true"></span>
                {{ trans_choice('{1} :count new|[2,*] :count new', $notificationCount, ['count' => $countDisplay]) }}
            @else
                {{ __('Clear') }}
            @endif
        </span>
    </div>

    @if($notifications->isEmpty())
        <x-user-not-found
            class="notification-dropdown__empty user-notification-dropdown__empty"
            :title="__('No new notifications')"
            :message="__('You are all caught up. New wallet alerts will appear here.')"
            icon="fa-bell-slash"
            :secure-label="__('Secure wallet alerts')"
        >
            <x-slot:preview>
                <div class="user-notification-empty-preview">
                    <span class="user-notification-empty-preview__icon">
                        <x-icon name="wallet-1" height="18" width="18"/>
                    </span>
                    <span class="user-notification-empty-preview__content">
                        <span class="user-notification-empty-preview__line"></span>
                        <span class="user-notification-empty-preview__line user-notification-empty-preview__line--short"></span>
                    </span>
                    <span class="user-notification-empty-preview__badge"></span>
                </div>
            </x-slot:preview>
        </x-user-not-found>
    @else
        <div class="notification-list notification-dropdown__list user-notification-dropdown__list" aria-live="polite">
            @foreach($visibleNotifications as $notification)
                @php
                    $data = (array) ($notification->data ?? []);
                    $actionType = NotificationActionType::tryFrom((string) ($data['action_type'] ?? ''));
                    $statusClass = $actionType?->class() ?? NotificationActionType::getClass((string) ($data['action_type'] ?? ''));
                    $actionLabel = $actionType ? __($actionType->label()) : __('Update');
                    $notificationIcon = $data['icon'] ?? 'bell';
                    $notificationTitle = $data['title'] ?? __('Notification');
                    $notificationMessage = $data['message'] ?? __('No additional details provided.');
                    $isUnread = $notification->read_at === null;
                @endphp

                <a href="{{  $data['action_link'] ?? 'javascript:void(0)'  }}" data-id="{{ $notification->id }}"
                   class="notification-item notification-dropdown__item user-notification-card user-notification-card--{{ $statusClass }} {{ $isUnread ? 'is-unread' : 'is-read' }} read-notification"
                   data-tone="{{ $statusClass }}"
                   role="menuitem">
                    <span class="user-notification-card__rail" aria-hidden="true"></span>
                    <span class="notification-dropdown__icon user-notification-card__icon" aria-hidden="true">
                        <x-icon name="{{ $notificationIcon }}" class="text-{{ $statusClass }}" height="24" width="24"/>
                    </span>
                    <span class="notification-dropdown__body user-notification-card__body">
                        <span class="notification-dropdown__row user-notification-card__meta">
                            <span class="user-notification-card__type-group">
                                <span class="user-notification-card__type">
                                    <span class="user-notification-card__pulse" aria-hidden="true"></span>
                                    {{ __('Wallet') }}
                                </span>
                                <span class="user-notification-card__status user-notification-card__status--{{ $statusClass }}">{{ $actionLabel }}</span>
                            </span>
                            <time class="notification-dropdown__time user-notification-card__time" datetime="{{ $notification->created_at->toIso8601String() }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </span>
                        <span class="notification-dropdown__item-title user-notification-card__title">{{ $notificationTitle }}</span>
                        <span class="notification-dropdown__message user-notification-card__message">{{ $notificationMessage }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    <div class="notification-dropdown__footer user-notification-dropdown__footer">
        @unless($notifications->isEmpty())
            <a href="{{ route('user.notifications.read-all') }}"
               class="notification-dropdown__action notification-dropdown__action--read user-notification-dropdown__action user-notification-dropdown__action--read">
                <i class="fa fa-check-double" aria-hidden="true"></i>
                {{ __('Mark all read') }}
            </a>
        @endunless

        <a href="{{ route('user.notifications.index') }}"
           class="notification-dropdown__action notification-dropdown__action--view user-notification-dropdown__action user-notification-dropdown__action--view">
            <i class="fa fa-eye" aria-hidden="true"></i>
            {{ __('View all') }}
        </a>
    </div>
</div>

@push('scripts')
    <script>
        'use strict';
        // Attach click event to mark notification as read
        $(document).on('click', '.read-notification', function () {
            'use strict';

            const notificationId = $(this).data('id');
            const url = "{{ route('user.notifications.markAsRead', ':id') }}".replace(':id', notificationId);

            // Make AJAX request to mark notification as read
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    _token: '{{ csrf_token() }}' // Ensure CSRF token is included
                },
                success: function (response) {
                    const notificationUrl = '{{ route('user.notifications.recent') }}';
                    $.get(notificationUrl, function (response) {
                        $('.append-new-notification').html(response); // Update notifications
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error marking notification as read:', textStatus, errorThrown);
                    // Optionally display an error message to the user
                }
            });
        });

    </script>
@endpush
