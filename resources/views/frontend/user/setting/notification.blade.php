@php
    use App\Enums\NotificationActionType;
    use App\Support\NotificationTuneLibrary;

    $preferenceState = $preferenceState ?? auth()->user()->notificationPreferenceState();
    $tuneOptions = $tuneOptions ?? NotificationTuneLibrary::tunes();
    $noteOptions = $noteOptions ?? NotificationTuneLibrary::noteOptions();
    $defaultTune = $defaultTune ?? NotificationTuneLibrary::resolve(NotificationTuneLibrary::defaultKey());
    $selectedTuneKey = $preferenceState['tune_key'] ?? 'default';
    $customTune = $preferenceState['custom_tune'] ?? NotificationTuneLibrary::defaultCustomTune();
    $customNotes = $customTune['notes'] ?? NotificationTuneLibrary::defaultCustomTune()['notes'];
@endphp
@extends('frontend.user.setting.index')
@section('title', __('Notifications'))

@section('user_setting_content')
<div class="notification-settings-page">
    <div class="card notification-card notification-preferences-card mb-3">
        <header class="notification-header notification-header--split">
            <div class="notification-header__copy">
                <span class="notification-header__icon" aria-hidden="true">
                    <i class="fa fa-sliders"></i>
                </span>
                <div>
                    <h5 class="notification-header__title">{{ __('Notification Preferences') }}</h5>
                    <p class="notification-header__subtitle">{{ __('Control account alerts, sound, and custom notification tune.') }}</p>
                </div>
            </div>
            <span class="notification-header__pill">
                <i class="fa-solid fa-circle" aria-hidden="true"></i>
                {{ __('Live') }}
            </span>
        </header>
        <div class="card-body notification-preferences-card__body">
            <form id="settings-notification-preferences-form" action="{{ route('user.notifications.preferences.update') }}" method="POST" class="notification-preferences-form">
                @csrf
                @method('PUT')

                <div class="notification-preferences-grid">
                    <div class="notification-toggle-stack">
                        <input type="hidden" name="notifications_enabled" value="0">
                        <div class="notification-toggle-card">
                            <input
                                class="notification-toggle-card__input"
                                type="checkbox"
                                role="switch"
                                name="notifications_enabled"
                                id="notifications_enabled"
                                value="1"
                                @checked($preferenceState['notifications_enabled'])
                            >
                            <span class="notification-toggle-switch" aria-hidden="true">
                                <span></span>
                            </span>
                            <label class="notification-toggle-card__body" for="notifications_enabled">
                                <span class="notification-toggle-card__title">{{ __('Enable in-app notifications') }}</span>
                                <span class="notification-toggle-card__text">{{ __('Show deposits, rewards, and security alerts inside your account.') }}</span>
                            </label>
                        </div>

                        <input type="hidden" name="tune_enabled" value="0">
                        <div class="notification-toggle-card">
                            <input
                                class="notification-toggle-card__input"
                                type="checkbox"
                                role="switch"
                                name="tune_enabled"
                                id="tune_enabled"
                                value="1"
                                @checked($preferenceState['tune_enabled'])
                            >
                            <span class="notification-toggle-switch" aria-hidden="true">
                                <span></span>
                            </span>
                            <label class="notification-toggle-card__body" for="tune_enabled">
                                <span class="notification-toggle-card__title">{{ __('Play notification tune') }}</span>
                                <span class="notification-toggle-card__text">{{ __('Play a short sound when important activity arrives.') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="notification-tune-card">
                        <label for="tune_key" class="form-label">{{ __('Tune') }}</label>
                        <div class="notification-tune-card__control">
                            <select id="tune_key" name="tune_key" class="form-select" data-user-tune-select>
                                <option value="default" @selected($selectedTuneKey === 'default')>
                                    {{ __('Platform default (:name)', ['name' => __($defaultTune['label'])]) }}
                                </option>
                                @foreach($tuneOptions as $key => $tune)
                                    <option value="{{ $key }}" @selected($selectedTuneKey === $key)>
                                        {{ __($tune['label']) }}
                                    </option>
                                @endforeach
                                <option value="{{ NotificationTuneLibrary::CUSTOM_KEY }}" @selected($selectedTuneKey === NotificationTuneLibrary::CUSTOM_KEY)>
                                    {{ __('My custom tune') }}
                                </option>
                            </select>
                            <button class="btn btn-outline-primary notification-preview-btn" type="button" data-user-tune-preview>
                                <i class="fa-solid fa-play" aria-hidden="true"></i>
                                {{ __('Preview') }}
                            </button>
                        </div>
                        @error('tune_key')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="notification-custom-tune" data-user-custom-tune-panel @class(['d-none' => $selectedTuneKey !== NotificationTuneLibrary::CUSTOM_KEY])>
                    <div class="notification-custom-tune__head">
                        <div>
                            <div class="notification-custom-tune__title">{{ __('Custom Tune Builder') }}</div>
                            <div class="notification-custom-tune__text">{{ __('Pick four notes and a speed to make your own notification sound.') }}</div>
                        </div>
                        <span class="notification-custom-tune__badge">{{ __('Personal') }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="custom_tune_label">{{ __('Tune Name') }}</label>
                            <input
                                type="text"
                                name="custom_tune[label]"
                                id="custom_tune_label"
                                class="form-control"
                                value="{{ old('custom_tune.label', $customTune['label'] ?? __('My Tune')) }}"
                                maxlength="40"
                                data-custom-tune-label
                            >
                            @error('custom_tune.label')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        @foreach(['note_1', 'note_2', 'note_3', 'note_4'] as $noteField)
                            <div class="col-6 col-md-2">
                                <label class="form-label" for="custom_tune_{{ $noteField }}">{{ __('Note :number', ['number' => (int) str_replace('note_', '', $noteField)]) }}</label>
                                <select
                                    name="custom_tune[{{ $noteField }}]"
                                    id="custom_tune_{{ $noteField }}"
                                    class="form-select"
                                    data-custom-tune-note
                                >
                                    @foreach($noteOptions as $noteKey => $note)
                                        <option
                                            value="{{ $noteKey }}"
                                            data-frequency="{{ $note['frequency'] }}"
                                            @selected(old("custom_tune.$noteField", $customNotes[$noteField] ?? null) === $noteKey)
                                        >
                                            {{ __($note['label']) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("custom_tune.$noteField")
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <div class="col-md-4">
                            <label class="form-label" for="custom_tune_speed">{{ __('Note Length') }}</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    min="80"
                                    max="360"
                                    name="custom_tune[speed]"
                                    id="custom_tune_speed"
                                    class="form-control"
                                    value="{{ old('custom_tune.speed', $customNotes['speed'] ?? 150) }}"
                                    data-custom-tune-speed
                                >
                                <span class="input-group-text">{{ __('ms') }}</span>
                            </div>
                            @error('custom_tune.speed')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <footer class="notification-preferences-actions">
                    <p class="notification-preferences-actions__hint">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        {{ __('Preferences are saved to this account and can be changed anytime.') }}
                    </p>
                    <button type="submit" class="btn btn-primary notification-preferences-actions__submit">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                        <span>{{ __('Save Preferences') }}</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <div class="card notification-card notification-feed-card">
        <header class="notification-header notification-header--split">
            <div class="notification-header__copy">
                <span class="notification-header__icon" aria-hidden="true">
                    <i class="fa fa-bell"></i>
                </span>
                <div>
                    <h5 class="notification-header__title">{{ __('Notifications') }}</h5>
                    <p class="notification-header__subtitle">{{ __('Recent account activity and wallet updates.') }}</p>
                </div>
            </div>
            <span class="notification-header__pill">
                {{ trans_choice('{0} Clear|{1} :count alert|[2,*] :count alerts', $notifications->count(), ['count' => $notifications->count()]) }}
            </span>
        </header>
        <div class="card-body p-0">
            @if($notifications->isNotEmpty())
                <ul class="notification-all-list notification-feed-list list-group list-group-flush">
                    @foreach($notifications as $notification)
                        @php
                            $data   = $notification->data;
                            $isRead = ! is_null($notification->read_at);
                        @endphp

                        <li class="notification-item notification-feed-item list-group-item">
                            <div class="notification-item__icon">
                                <x-icon
                                    name="{{ $data['icon'] }}"
                                    class="notification-item__icon-svg text-{{ NotificationActionType::getClass($data['action_type']) }}"
                                    height="38"
                                    width="38"
                                />
                            </div>

                            <div class="notification-item__content">
                                <div class="notification-item__title">
                                    {{ $data['title'] ?? __('No message available') }}
                                </div>
                                <div class="notification-item__message">
                                    {{ $data['message'] }}
                                </div>
                            </div>

                            <div class="notification-item__meta">
                                <time class="notification-item__time">
                                    {{ $notification->created_at->format('d M, Y H:i A') }}
                                </time>

                                @unless($isRead)
                                    <a
                                        href="{{ route('user.notifications.markAsRead', $notification->id) }}"
                                        class="notification-item__mark-read"
                                        title="{{ __('Mark as read') }}"
                                        aria-label="{{ __('Mark notification as read') }}"
                                    >
                                        <i class="fa-sharp fa-solid fa-circle-check" aria-hidden="true"></i>
                                    </a>
                                @endunless
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if($notifications->hasPages())
                    <footer class="notification-footer card-footer bg-white text-center">
                        {{ $notifications->links() }}
                    </footer>
                @endif
            @else
                <x-user-not-found
                    :title="__('No notifications found')"
                    :message="__('Updates, alerts, and account activity will appear here when available.')"
                    :eyebrow="__('Inbox clear')"
                    icon="fa-bell"
                    class="m-3"
                />
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const $select = $('[data-user-tune-select]');
            const $customPanel = $('[data-user-custom-tune-panel]');

            const toggleCustomPanel = function () {
                $customPanel.toggleClass('d-none', $select.val() !== '{{ NotificationTuneLibrary::CUSTOM_KEY }}');
            };

            const buildCustomTune = function () {
                const speed = Math.max(80, Math.min(360, parseInt($('[data-custom-tune-speed]').val(), 10) || 150));
                const steps = [];

                $('[data-custom-tune-note]').each(function () {
                    const frequency = Number($(this).find(':selected').data('frequency') || 0);
                    steps.push({
                        frequency: frequency,
                        duration: Number((speed / 1000).toFixed(3)),
                        type: 'sine',
                        volume: 0.16,
                        gap: 0.035
                    });
                });

                return {
                    key: '{{ NotificationTuneLibrary::CUSTOM_KEY }}',
                    label: $('[data-custom-tune-label]').val() || @json(__('My Tune')),
                    custom: true,
                    steps: steps
                };
            };

            const previewSelected = function () {
                if (!window.DigiKashNotificationTune) {
                    return;
                }

                if ($select.val() === '{{ NotificationTuneLibrary::CUSTOM_KEY }}') {
                    window.DigiKashNotificationTune.preview(buildCustomTune());

                    return;
                }

                const tune = $select.val() === 'default'
                    ? window.DigiKashNotificationTune.getDefaultTune()
                    : window.DigiKashNotificationTune.getTune($select.val());

                window.DigiKashNotificationTune.preview(tune);
            };

            $select.on('change', toggleCustomPanel);
            $(document).on('click', '[data-user-tune-preview]', previewSelected);
            toggleCustomPanel();
        })();
    </script>
@endpush
