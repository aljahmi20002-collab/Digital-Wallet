@if ($section === 'notification_settings')
    <button type="button"
            class="btn btn-outline-primary settings-site-header-action"
            data-notification-tune-settings-preview>
        <i class="fa-solid fa-play me-1"></i>
        {{ __('Preview Tune') }}
    </button>
@endif

@pushOnce('scripts')
    <script>
        'use strict';

        $(document)
            .off('click.notificationTuneSettingsPreview', '[data-notification-tune-settings-preview]')
            .on('click.notificationTuneSettingsPreview', '[data-notification-tune-settings-preview]', function () {
                const tunePlayer = window.DigiKashNotificationTune;
                const selectedTune = $('#notification_tune_default').val();

                if (!tunePlayer || !selectedTune) {
                    return;
                }

                tunePlayer.preview(tunePlayer.getTune(selectedTune));
            });
    </script>
@endPushOnce
