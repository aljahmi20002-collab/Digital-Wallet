<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\NotificationTuneLibrary;
use Illuminate\Database\Seeder;

class NotificationTuneSettingSeeder extends Seeder
{
    /**
     * Seed notification tune defaults without overwriting admin choices.
     */
    public function run(): void
    {
        $this->seedMissingSetting('notification_delivery_enabled', true, 'bool');
        $this->seedMissingSetting('notification_tune_sound_enabled', true, 'bool');
        $this->seedMissingSetting('notification_tune_default', NotificationTuneLibrary::DEFAULT_KEY, 'string');

        Setting::flushCache();
    }

    private function seedMissingSetting(string $key, mixed $value, string $type): void
    {
        Setting::query()->firstOrCreate(
            ['key' => $key],
            ['val' => $value, 'type' => $type]
        );
    }
}
