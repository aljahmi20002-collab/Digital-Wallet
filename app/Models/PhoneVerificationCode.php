<?php

namespace App\Models;

use Database\Factories\PhoneVerificationCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneVerificationCode extends Model
{
    /** @use HasFactory<PhoneVerificationCodeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'code_hash',
        'attempts',
        'sent_at',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts'    => 'integer',
            'sent_at'     => 'datetime',
            'expires_at'  => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
