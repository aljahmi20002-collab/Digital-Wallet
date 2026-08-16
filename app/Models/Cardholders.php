<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use App\Enums\KycStatus;
use App\Enums\VirtualCard\CardholderStatus;
use App\Enums\VirtualCard\CardholderType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cardholders extends Model
{
    protected $table = 'cardholders';

    protected $fillable = [
        'user_id',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'mobile',
        'phone_country_code',
        'gender',
        'dob',
        'nationality',
        'place_of_birth',
        'relation',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'id_type',
        'id_number',
        'id_issue_country',
        'id_issue_date',
        'id_expiry',
        'tax_id',
        'tax_country',
        'occupation',
        'employer',
        'annual_income',
        'source_of_funds',
        'pep_flag',
        'sanctions_flag',
        'card_type',
        'businesses_id',
        'kyc_status',
        'kyc_type',
        'address_proof_type',
        'kyc_documents',
        'note',
        'status',
    ];

    protected $casts = [
        'kyc_documents'  => 'array',
        'dob'            => 'date',
        'id_issue_date'  => 'date',
        'id_expiry'      => 'date',
        'annual_income'  => 'decimal:2',
        'pep_flag'       => 'boolean',
        'sanctions_flag' => 'boolean',
        'status'         => CardholderStatus::class,
        'kyc_status'     => KycStatus::class,
        'card_type'      => CardholderType::class,
        'gender'         => Gender::class,
    ];

    // Cardholder type enum accessor
    public function cardType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? CardholderType::from($value) : CardholderType::PERSONAL,
            set: fn ($value) => $value instanceof CardholderType ? $value->value : $value,
        );
    }

    // Business relation (only if card_type = business)
    public function business(): BelongsTo
    {
        return $this->belongsTo(Businesses::class, 'businesses_id');
    }

    // User relation
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kycTemplate()
    {
        return $this->belongsTo(KycTemplate::class, 'kyc_type');
    }

    // Full name accessor — includes middle name when present so it matches
    // the legal name expected by every provider.
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    /**
     * Provider-agnostic identity payload.
     *
     * Every virtual-card provider integration should call this method
     * instead of reading individual columns. New providers (Marqeta,
     * Adyen, Lithic, etc.) only have to map these keys to their own
     * field names — they never touch the schema directly.
     */
    public function toProviderPayload(): array
    {
        return [
            'card_type' => $this->card_type instanceof CardholderType
                ? $this->card_type->value
                : (string) $this->card_type,

            // Identity
            'title'          => $this->title,
            'first_name'     => $this->first_name,
            'middle_name'    => $this->middle_name,
            'last_name'      => $this->last_name,
            'full_name'      => $this->full_name,
            'gender'         => $this->gender?->value,
            'date_of_birth'  => optional($this->dob)->format('Y-m-d'),
            'nationality'    => $this->nationality,
            'place_of_birth' => $this->place_of_birth,

            // Contact
            'email'              => $this->email,
            'phone'              => $this->mobile,
            'phone_country_code' => $this->phone_country_code,

            // Address (billing)
            'address' => [
                'line1'       => $this->address_line1,
                'line2'       => $this->address_line2,
                'city'        => $this->city,
                'state'       => $this->state,
                'postal_code' => $this->postal_code,
                'country'     => $this->country,
            ],

            // Government ID
            'identification' => [
                'type'          => $this->id_type,
                'number'        => $this->id_number,
                'issue_country' => $this->id_issue_country,
                'issue_date'    => optional($this->id_issue_date)->format('Y-m-d'),
                'expiry'        => optional($this->id_expiry)->format('Y-m-d'),
            ],

            // Tax
            'tax' => [
                'id'      => $this->tax_id,
                'country' => $this->tax_country,
            ],

            // Employment / AML
            'employment' => [
                'occupation'      => $this->occupation,
                'employer'        => $this->employer,
                'annual_income'   => $this->annual_income,
                'source_of_funds' => $this->source_of_funds,
            ],

            // Compliance
            'compliance' => [
                'pep'       => (bool) $this->pep_flag,
                'sanctions' => (bool) $this->sanctions_flag,
            ],

            // KYC docs (templated map)
            'kyc' => [
                'template_id' => $this->kyc_type,
                'status'      => $this->kyc_status?->value ?? $this->kyc_status,
                'documents'   => $this->kyc_documents      ?? [],
            ],

            // Business block (only meaningful when card_type=business)
            'business' => $this->business?->toProviderPayload(),
        ];
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getBusinessAddressAttribute($value)
    {
        return $this->business ? $this->business->full_address : $value;
    }

    // ===== Query Scopes =====
    public function scopeStatus($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                // The users table on this project has `first_name` /
                // `last_name` / `username` (no `name` column). Searching
                // `users.name` blows up with "Unknown column 'name' in
                // 'where clause'", so we fan the search out across the
                // actual columns instead.
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('mobile', 'like', "%$search%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%$search%")
                        ->orWhere('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%"))
                    ->orWhereHas('business', fn ($b) => $b->where('business_name', 'like', "%$search%"));
            });
        }
    }
    // Optionally: scopeDateRange for future
}
