<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackgroundTaskLog extends Model
{
    protected $fillable = [
        'task_key',
        'command_signature',
        'status',
        'options',
        'output',
        'error_message',
        'started_at',
        'finished_at',
        'duration_ms',
        'executed_by',
        'trigger_type',
    ];

    protected function casts(): array
    {
        return [
            'options'     => 'array',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'executed_by');
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function durationLabel(): string
    {
        if ($this->duration_ms === null) {
            return '-';
        }

        if ($this->duration_ms < 1000) {
            return $this->duration_ms.' ms';
        }

        return number_format($this->duration_ms / 1000, 2).' s';
    }

    public function outputSummary(int $maxLength = 120): string
    {
        $text = $this->isFailed() ? ($this->error_message ?? '') : ($this->output ?? '');
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        if ($text === '') {
            return 'No output';
        }

        return mb_strlen($text) > $maxLength
            ? mb_substr($text, 0, $maxLength).'...'
            : $text;
    }
}
