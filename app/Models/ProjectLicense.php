<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ProjectLicense extends Model
{
    protected $fillable = [
        'product_slug',
        'item_id',
        'purchase_code',
        'license_token',
        'buyer_username',
        'domain',
        'status',
        'support_until',
        'activated_at',
        'last_checked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'purchase_code'   => 'encrypted',
            'support_until'   => 'datetime',
            'activated_at'    => 'datetime',
            'last_checked_at' => 'datetime',
            'metadata'        => 'array',
        ];
    }

    public static function current(): ?self
    {
        return self::query()
            ->where('product_slug', config('project_updater.product_slug'))
            ->latest('id')
            ->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function supportStatusLabel(): string
    {
        if (! $this->support_until instanceof Carbon) {
            return __('Unknown');
        }

        if ($this->support_until->isPast()) {
            return __('Support expired');
        }

        if ($this->supportExpiresSoon()) {
            return __('Support ending soon');
        }

        return __('Supported');
    }

    public function supportTone(): string
    {
        if (! $this->support_until instanceof Carbon) {
            return 'secondary';
        }

        if ($this->support_until->isPast()) {
            return 'danger';
        }

        if ($this->supportExpiresSoon()) {
            return 'warning';
        }

        return 'success';
    }

    public function supportDaysRemaining(): ?int
    {
        if (! $this->support_until instanceof Carbon) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->support_until->copy()->startOfDay(), false);
    }

    public function supportExpiresSoon(int $days = 30): bool
    {
        $daysRemaining = $this->supportDaysRemaining();

        return $daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= $days;
    }
}
