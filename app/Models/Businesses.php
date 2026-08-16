<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KycStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Businesses extends Model
{
    protected $table = 'businesses';

    protected $fillable = [
        'user_id',
        'business_name',
        'trading_name',
        'registration_number',
        'tin',
        'business_type',
        'incorporation_date',
        'incorporation_country',
        'industry',
        'mcc_code',
        'website_url',
        'contact_email',
        'contact_phone',
        'phone_country_code',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'documents',
        'beneficial_owners',
        'kyc_status',
        'status',
    ];

    protected $casts = [
        'documents'          => 'array',
        'beneficial_owners'  => 'array',
        'incorporation_date' => 'date',
        'kyc_status'         => KycStatus::class,
        'status'             => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Business full address accessor
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

    /**
     * Provider-agnostic business payload, mirroring Cardholders::toProviderPayload().
     */
    public function toProviderPayload(): array
    {
        return [
            'legal_name'            => $this->business_name,
            'trading_name'          => $this->trading_name,
            'registration_number'   => $this->registration_number,
            'tax_id'                => $this->tin,
            'business_type'         => $this->business_type,
            'incorporation_date'    => optional($this->incorporation_date)->format('Y-m-d'),
            'incorporation_country' => $this->incorporation_country,
            'industry'              => $this->industry,
            'mcc_code'              => $this->mcc_code,
            'website_url'           => $this->website_url,
            'contact'               => [
                'email'              => $this->contact_email,
                'phone'              => $this->contact_phone,
                'phone_country_code' => $this->phone_country_code,
            ],
            'address' => [
                'line1'       => $this->address_line1,
                'line2'       => $this->address_line2,
                'city'        => $this->city,
                'state'       => $this->state,
                'postal_code' => $this->postal_code,
                'country'     => $this->country,
            ],
            'beneficial_owners' => $this->beneficial_owners ?? [],
            'documents'         => $this->documents         ?? [],
        ];
    }
}
