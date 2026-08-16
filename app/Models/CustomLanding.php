<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomLanding extends Model
{
    protected $fillable = [
        'file_count',
        'folder',
        'html_updated_at',
        'last_validated_at',
        'name',
        'published_at',
        'source_checksum',
        'status',
        'total_size',
    ];

    protected function casts(): array
    {
        return [
            'html_updated_at'   => 'datetime',
            'last_validated_at' => 'datetime',
            'published_at'      => 'datetime',
            'status'            => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public static function getActiveLanding(): ?self
    {
        return self::query()->active()->latest('updated_at')->first();
    }

    public function landingPath(): string
    {
        return public_path("custom-landings/{$this->folder}");
    }

    public function indexPath(): string
    {
        return $this->landingPath().DIRECTORY_SEPARATOR.'index.html';
    }

    public function publicUrl(): string
    {
        return asset("custom-landings/{$this->folder}/index.html");
    }

    public function hasIndexFile(): bool
    {
        return file_exists($this->indexPath());
    }
}
