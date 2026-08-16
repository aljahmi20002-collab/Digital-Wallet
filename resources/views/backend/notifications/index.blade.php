@extends('backend.layouts.app')

@section('title', __('Notifications'))

@php
    use App\Enums\NotificationActionType;

    $totalNotifications  = $notifications->total();
    $unreadNotifications = auth()->user()->unreadNotifications()->count();
    $readNotifications   = auth()->user()->readNotifications()->count();
    $activeFilter        = $filter ?? 'all';
    $filterTabs = [
        'all'    => ['label' => __('All'),    'count' => $unreadNotifications + $readNotifications],
        'unread' => ['label' => __('Unread'), 'count' => $unreadNotifications],
        'read'   => ['label' => __('Read'),   'count' => $readNotifications],
    ];
@endphp

@section('content')
    <section class="notification-center notification-center--compact">
        <header class="notification-center__header">
            <div class="notification-center__heading">
                <span class="notification-center__eyebrow">{{ __('Admin Center') }}</span>
                <h1 class="notification-center__title">{{ __('Notification Center') }}</h1>
                <p class="notification-center__subtitle">
                    {{ trans_choice('{0} You are all caught up|{1} :count notification in your inbox|[2,*] :count notifications in your inbox', $unreadNotifications + $readNotifications, ['count' => number_format($unreadNotifications + $readNotifications)]) }}
                    @if($unreadNotifications > 0)
                        <span class="notification-center__subtitle-divider" aria-hidden="true">·</span>
                        <span class="notification-center__subtitle-accent">{{ trans_choice(':count unread|:count unread', $unreadNotifications, ['count' => number_format($unreadNotifications)]) }}</span>
                    @endif
                </p>
            </div>

            <div class="notification-center__actions">
                @if($unreadNotifications > 0)
                    <a href="{{ route('admin.notifications.markAllAsRead') }}"
                       class="notification-center__action notification-center__action--primary">
                        <i class="fa-regular fa-circle-check"></i>
                        <span>{{ __('Mark all as read') }}</span>
                    </a>
                @else
                    <span class="notification-center__action notification-center__action--quiet" aria-disabled="true">
                        <i class="fa-regular fa-circle-check"></i>
                        <span>{{ __('Inbox is clear') }}</span>
                    </span>
                @endif
            </div>
        </header>

        <nav class="notification-center__filter" aria-label="{{ __('Notification filters') }}">
            @foreach($filterTabs as $key => $tab)
                <a href="{{ route('admin.notifications.index', $key === 'all' ? [] : ['filter' => $key]) }}"
                   class="notification-center__filter-tab @if($activeFilter === $key) is-active @endif"
                   aria-current="{{ $activeFilter === $key ? 'page' : 'false' }}">
                    <span class="notification-center__filter-label">{{ $tab['label'] }}</span>
                    <span class="notification-center__filter-count">{{ number_format($tab['count']) }}</span>
                </a>
            @endforeach
        </nav>

        @if($notifications->isEmpty())
            <x-admin-not-found
                :title="$activeFilter === 'unread' ? __('No unread notifications') : ($activeFilter === 'read' ? __('No read notifications yet') : __('No notifications found'))"
                :message="__('System notifications and alerts will appear here when available.')"
                icon="fa-bell"
                class="notification-center__empty"
            />
        @else
            <div class="notification-center__list" role="list" aria-live="polite">
                @foreach($notifications as $notification)
                    @php
                        $data         = (array) ($notification->data ?? []);
                        $sender       = (array) ($data['sender'] ?? []);
                        $senderName   = $sender['name'] ?? __('System Notification');
                        $senderAvatar = $sender['avatar'] ?? null;
                        $title        = $data['title'] ?? __('Notification');
                        $bodyMessage  = $data['message'] ?? __('No additional details provided.');
                        $actionLink   = $data['action_link'] ?? 'javascript:void(0);';
                        $isUnread     = is_null($notification->read_at);

                        $tone = isset($data['action_type']) && $data['action_type'] !== ''
                            ? NotificationActionType::getClass((string) $data['action_type'])
                            : 'primary';
                        $actionLabel = isset($data['action_type']) && $data['action_type'] !== ''
                            ? __(ucfirst(str_replace('_', ' ', (string) $data['action_type'])))
                            : __('Update');

                        $initial = mb_strtoupper(mb_substr(trim((string) $senderName), 0, 1, 'UTF-8') ?: 'N', 'UTF-8');
                        $bodyNorm   = mb_strtolower(trim((string) $bodyMessage), 'UTF-8');
                        $senderNorm = mb_strtolower(trim((string) $senderName), 'UTF-8');
                        $showSender = $senderNorm !== ''
                            && ($senderName === __('System Notification')
                                || $bodyNorm === ''
                                || ! str_contains($bodyNorm, $senderNorm));
                    @endphp

                    <article class="notification-card notification-card--{{ $tone }} {{ $isUnread ? 'notification-card--unread' : 'notification-card--read' }}"
                             role="listitem"
                             data-id="{{ $notification->id }}">
                        <a href="{{ safeUrl($actionLink) }}"
                           class="notification-card__link read-notification"
                           data-id="{{ $notification->id }}"
                           aria-label="{{ $title }}">
                            <span class="notification-card__indicator" aria-hidden="true"></span>

                            <div class="notification-card__avatar">
                                @if($senderAvatar)
                                    <img src="{{ asset($senderAvatar) }}" alt="{{ $senderName }}" class="notification-card__avatar-img" loading="lazy">
                                @else
                                    <span class="notification-card__avatar-fallback notification-card__avatar-fallback--{{ $tone }}">{{ $initial }}</span>
                                @endif
                                @if($isUnread)
                                    <span class="notification-card__avatar-dot notification-card__avatar-dot--{{ $tone }}" aria-hidden="true"></span>
                                @endif
                            </div>

                            <div class="notification-card__body">
                                <div class="notification-card__head">
                                    <h2 class="notification-card__title">{{ $title }}</h2>
                                    <span class="notification-card__pill notification-card__pill--{{ $tone }}">{{ $actionLabel }}</span>
                                </div>

                                <p class="notification-card__message">{{ $bodyMessage }}</p>

                                <div class="notification-card__meta">
                                    @if($showSender)
                                        <span class="notification-card__sender">
                                            <span class="notification-card__sender-dot notification-card__sender-dot--{{ $tone }}" aria-hidden="true"></span>
                                            {{ $senderName }}
                                        </span>
                                    @endif
                                    <span class="notification-card__time" title="{{ $notification->created_at->format('Y-m-d H:i') }}">
                                        <i class="fa-regular fa-clock notification-card__time-icon" aria-hidden="true"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <div class="notification-card__trail">
                                @if($isUnread)
                                    <span class="notification-card__badge" aria-label="{{ __('Unread') }}">
                                        <span class="notification-card__badge-dot" aria-hidden="true"></span>
                                        {{ __('New') }}
                                    </span>
                                @endif
                                <time class="notification-card__timestamp" datetime="{{ $notification->created_at->toIso8601String() }}">
                                    {{ $notification->created_at->format('d M · h:i A') }}
                                </time>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            @if($notifications->hasPages() || $notifications->total() > 0)
                <footer class="notification-center__footer">
                    <span class="notification-center__pagination-info">
                        {{ __('Showing :from – :to of :total', [
                            'from'  => $notifications->firstItem(),
                            'to'    => $notifications->lastItem(),
                            'total' => $notifications->total(),
                        ]) }}
                    </span>
                    @if($notifications->hasPages())
                        <nav class="notification-center__pagination" aria-label="{{ __('Notifications pagination') }}">
                            {{ $notifications->onEachSide(1)->links() }}
                        </nav>
                    @endif
                </footer>
            @endif
        @endif
    </section>
@endsection

@push('scripts')
    @include('backend.notifications.partials._scripts')
@endpush
