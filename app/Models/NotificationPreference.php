<?php

namespace App\Models;

use App\Support\NotificationTuneLibrary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'notifications_enabled',
        'tune_enabled',
        'tune_key',
        'custom_tune',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notifications_enabled' => 'boolean',
            'tune_enabled'          => 'boolean',
            'custom_tune'           => 'array',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function updateFor(EloquentModel $notifiable, array $attributes): self
    {
        return self::query()->updateOrCreate([
            'notifiable_type' => $notifiable->getMorphClass(),
            'notifiable_id'   => $notifiable->getKey(),
        ], $attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function tunePayload(): array
    {
        return NotificationTuneLibrary::resolve(
            $this->tune_key,
            $this->custom_tune
        );
    }
}
