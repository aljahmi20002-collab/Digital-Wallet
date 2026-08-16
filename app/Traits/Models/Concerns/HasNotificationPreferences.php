<?php

namespace App\Traits\Models\Concerns;

use App\Models\NotificationPreference;
use App\Support\NotificationTuneLibrary;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasNotificationPreferences
{
    public function notificationPreference(): MorphOne
    {
        return $this->morphOne(NotificationPreference::class, 'notifiable');
    }

    public function notificationDeliveryEnabled(): bool
    {
        $preference = $this->notificationPreference;

        if ($preference) {
            return (bool) $preference->notifications_enabled;
        }

        return (bool) setting('notification_delivery_enabled', true);
    }

    public function notificationSoundEnabled(): bool
    {
        if (! $this->notificationDeliveryEnabled()) {
            return false;
        }

        $preference = $this->notificationPreference;

        if ($preference) {
            return (bool) $preference->tune_enabled;
        }

        return (bool) setting('notification_tune_sound_enabled', true);
    }

    public function notificationTuneKey(): string
    {
        $preference = $this->notificationPreference;

        if ($preference && filled($preference->tune_key)) {
            return NotificationTuneLibrary::normalizeKey($preference->tune_key);
        }

        return NotificationTuneLibrary::defaultKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function notificationTunePayload(): array
    {
        $preference = $this->notificationPreference;

        if ($preference) {
            return $preference->tunePayload();
        }

        return NotificationTuneLibrary::resolve(NotificationTuneLibrary::defaultKey());
    }

    /**
     * @return array<string, mixed>
     */
    public function notificationPreferenceState(): array
    {
        $preference = $this->notificationPreference;

        return [
            'notifications_enabled' => $preference
                ? (bool) $preference->notifications_enabled
                : (bool) setting('notification_delivery_enabled', true),
            'tune_enabled' => $preference
                ? (bool) $preference->tune_enabled
                : (bool) setting('notification_tune_sound_enabled', true),
            'tune_key'    => $preference?->tune_key ?: 'default',
            'custom_tune' => $preference?->custom_tune ?: NotificationTuneLibrary::defaultCustomTune(),
            'tune'        => $this->notificationTunePayload(),
        ];
    }
}
