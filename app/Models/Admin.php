<?php

namespace App\Models;

use App\Traits\Models\Concerns\HasNotificationPreferences;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory;
    use HasNotificationPreferences;
    use HasRoles;
    use Notifiable;

    protected string $guard_name = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'avatar',
        'name',
        'email',
        'google2fa_secret',
        'two_factor_enabled',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAvatarAltAttribute(): string
    {
        return $this->avatar ?? '/general/static/default/admin.png';
    }

    public function getRecentNotifications(): Collection
    {
        if (! $this->notificationDeliveryEnabled()) {
            return collect();
        }

        return $this->unreadNotifications()->latest()->get();
    }

    /**
     * Get the attributes that should be cast.
     *
     * This method defines the attributes that should be cast to a specific type.
     *
     * @return array Returns an array of attribute names and their corresponding types.
     */
    protected function casts(): array
    {
        // Define the attributes that should be cast
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'status'            => 'boolean',
            'dismissed_notices' => 'array',
        ];
    }

    /**
     * Has the admin permanently dismissed the given dashboard notice?
     */
    public function hasDismissedNotice(string $key): bool
    {
        return in_array($key, (array) ($this->dismissed_notices ?? []), true);
    }

    /**
     * Mark a dashboard notice as permanently dismissed for this admin.
     */
    public function dismissNotice(string $key): void
    {
        $current = (array) ($this->dismissed_notices ?? []);
        if (in_array($key, $current, true)) {
            return;
        }
        $current[]               = $key;
        $this->dismissed_notices = array_values(array_unique($current));
        $this->save();
    }
}
