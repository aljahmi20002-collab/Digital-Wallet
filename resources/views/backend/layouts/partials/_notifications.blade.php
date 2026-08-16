@php
    use App\Enums\NotificationActionType;

    $notificationCount = $notifications->count();
    $hasNotifications = $notificationCount > 0;
    $countDisplay = $notificationCount > 99 ? '99+' : (string) $notificationCount;
@endphp
<a class="nav-link admin-notification-trigger {{ $hasNotifications ? 'has-unread' : '' }}"
   id="adminNotificationTrigger"
   data-coreui-toggle="dropdown"
   href="#"
   role="button"
   aria-haspopup="true"
   aria-expanded="false"
   aria-label="{{ trans_choice('{0} No new notifications|{1} :count new notification|[2,*] :count new notifications', $notificationCount, ['count' => $notificationCount]) }}">
    <span class="admin-notification-trigger__icon">
        <x-icon name="cil-bell" class="icon icon-lg"/>
    </span>
    @if($hasNotifications)
        <span class="admin-notification-counter" aria-hidden="true">{{ $countDisplay }}</span>
        <span class="visually-hidden">{{ trans_choice(':count new notification|:count new notifications', $notificationCount, ['count' => $notificationCount]) }}</span>
    @endif
</a>

<div class="dropdown-menu dropdown-menu-end p-0 notification-dropdown notification-dropdown--pro"
     aria-labelledby="adminNotificationTrigger"
     role="menu"
     data-coreui-display="static">
    <div class="notification-dropdown__head">
        <span class="notification-dropdown__head-icon" aria-hidden="true">
            <x-icon name="cil-bell" class="icon"/>
        </span>
        <div class="notification-dropdown__head-text">
            <span class="notification-dropdown__eyebrow">{{ __('Admin Center') }}</span>
            <span class="notification-dropdown__title">{{ __('Notifications') }}</span>
            <span class="notification-dropdown__subtitle">
                {{ trans_choice('{0} You are all caught up|{1} You have :count new notification|[2,*] You have :count new notifications', $notificationCount, ['count' => $notificationCount]) }}
            </span>
        </div>
        <span class="notification-dropdown__status {{ $hasNotifications ? 'is-live' : 'is-clear' }}">
            @if($hasNotifications)
                <span class="notification-dropdown__status-dot" aria-hidden="true"></span>
                <span class="notification-dropdown__count-badge">{{ $countDisplay }}</span>
            @else
                <span>{{ __('Clear') }}</span>
            @endif
        </span>
    </div>

    @if($notifications->isEmpty())
        <x-admin-not-found
            class="notification-dropdown__empty"
            :title="__('All caught up!')"
            :message="__('You have no new notifications right now.')"
            icon="fa-bell-slash"
        />
    @else
        <div class="scrollable-notification-list notification-dropdown__list" aria-live="polite">
            @foreach($notifications->take(10) as $notification)
                @php
                    $payload = (array) ($notification->data ?? []);
                    $sender = (array) ($payload['sender'] ?? []);
                    $senderName = $sender['name'] ?? __('System Notification');
                    $senderAvatar = $sender['avatar'] ?? null;
                    $bodyNorm = mb_strtolower(trim((string) ($payload['message'] ?? '')), 'UTF-8');
                    $senderNorm = mb_strtolower(trim((string) $senderName), 'UTF-8');
                    $showSender = $senderNorm !== ''
                        && (
                            $senderName === __('System Notification')
                            || $bodyNorm === ''
                            || ! str_contains($bodyNorm, $senderNorm)
                        );
                    $tone = isset($payload['action_type']) && $payload['action_type'] !== ''
                        ? NotificationActionType::getClass((string) $payload['action_type'])
                        : 'primary';
                    $actionLabel = isset($payload['action_type']) && $payload['action_type'] !== ''
                        ? __(ucfirst(str_replace('_', ' ', (string) $payload['action_type'])))
                        : __('Update');
                    $initial = mb_strtoupper(mb_substr(trim((string) $senderName), 0, 1, 'UTF-8') ?: 'N', 'UTF-8');
                @endphp
                <a class="dropdown-item notification-dropdown-item notification-dropdown-item--pro notification-dropdown-item--{{ $tone }} read-notification"
                   href="{{ safeUrl($payload['action_link'] ?? null, '#') }}"
                   role="menuitem"
                   data-id="{{ $notification->id }}">
                    <div class="notification-avatar notification-dropdown-item__avatar"
                         data-coreui-toggle="tooltip"
                         data-coreui-placement="top"
                         title="{{ $senderName }}">
                        @if($senderAvatar)
                            <img src="{{ asset($senderAvatar) }}"
                                 alt="{{ $senderName }}"
                                 class="notification-avatar-img" loading="lazy">
                        @else
                            <span class="notification-avatar-img notification-avatar-img--fallback notification-avatar-img--{{ $tone }}">{{ $initial }}</span>
                        @endif
                        <span class="notification-dropdown-item__dot" aria-hidden="true"></span>
                    </div>
                    <div class="notification-content notification-dropdown-item__main">
                        <span class="notification-dropdown-item__title-wrap">
                            <span class="notification-title notification-dropdown-item__title">{{ $payload['title'] ?? __('Notification') }}</span>
                            <span class="notification-dropdown-item__type notification-dropdown-item__type--{{ $tone }}">{{ $actionLabel }}</span>
                        </span>
                        <span class="notification-message notification-dropdown-item__message">{{ $payload['message'] ?? __('No additional details provided.') }}</span>
                        <span class="notification-dropdown-item__meta">
                            @if($showSender)
                                <span class="notification-dropdown-item__sender">{{ $senderName }}</span>
                            @endif
                            <span class="notification-dropdown-item__time-badge"
                                  data-coreui-toggle="tooltip"
                                  data-coreui-placement="top"
                                  title="{{ $notification->created_at->format('Y-m-d H:i') }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="notification-dropdown__footer">
        @unless($notifications->isEmpty())
            <a href="{{ route('admin.notifications.markAllAsRead') }}"
               class="btn btn-sm notification-dropdown__action notification-dropdown__action--ghost">
                <x-icon name="ticket-check" class="icon notification-dropdown__action-icon"/>
                <span>{{ __('Mark all as read') }}</span>
            </a>
        @endunless
        <a href="{{ route('admin.notifications.index') }}"
           class="btn btn-sm notification-dropdown__action notification-dropdown__action--primary">
            <x-icon name="eye-1" class="icon notification-dropdown__action-icon"/>
            <span>{{ __('View all') }}</span>
        </a>
    </div>
</div>

@push('scripts')
    <script>
        'use strict';
        $(document).on('click', '.read-notification', function () {
            const notificationId = $(this).data('id');
            const url = "{{ route('admin.notifications.markAsRead', ':id') }}".replace(':id', notificationId);
            $.ajax({
                url: url,
                type: 'get',
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    const notificationUrl = '{{ route('admin.notifications.recent') }}';
                    $.get(notificationUrl, function (html) {
                        $('#append-new-admin-notification').html(html);
                    });
                },
                error: function (_jqXHR, textStatus, errorThrown) {
                    console.error('Error marking notification as read:', textStatus, errorThrown);
                }
            });
        });
    </script>
@endpush
