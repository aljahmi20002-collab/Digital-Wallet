<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUpdate extends Model
{
    protected $fillable = [
        'version',
        'channel',
        'status',
        'package_url',
        'checksum',
        'signature',
        'changelog',
        'requirements',
        'package_path',
        'backup_path',
        'release_date',
        'checked_at',
        'installed_at',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'changelog'    => 'array',
            'requirements' => 'array',
            'metadata'     => 'array',
            'release_date' => 'datetime',
            'checked_at'   => 'datetime',
            'installed_at' => 'datetime',
        ];
    }

    public function isInstallable(): bool
    {
        return in_array($this->status, ['available', 'downloaded', 'failed'], true);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'installed'  => 'success',
            'failed'     => 'danger',
            'downloaded' => 'info',
            'current'    => 'secondary',
            default      => 'warning',
        };
    }
}
