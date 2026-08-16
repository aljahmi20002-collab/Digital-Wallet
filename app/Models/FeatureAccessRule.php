<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FeatureAccessRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int        $id
 * @property int        $feature_id
 * @property string     $panel
 * @property bool       $is_visible
 * @property bool       $is_accessible
 * @property array|null $conditions
 * @property Feature    $feature
 */
class FeatureAccessRule extends Model
{
    /** @use HasFactory<FeatureAccessRuleFactory> */
    use HasFactory;

    public const PANEL_USER = 'user';

    public const PANEL_MERCHANT = 'merchant';

    public const PANEL_AGENT = 'agent';

    public const PANELS = [
        self::PANEL_USER,
        self::PANEL_MERCHANT,
        self::PANEL_AGENT,
    ];

    protected $fillable = [
        'feature_id',
        'panel',
        'is_visible',
        'is_accessible',
        'conditions',
    ];

    protected function casts(): array
    {
        return [
            'is_visible'    => 'boolean',
            'is_accessible' => 'boolean',
            'conditions'    => 'array',
        ];
    }

    /**
     * @return BelongsTo<Feature, FeatureAccessRule>
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function requiresKyc(): bool
    {
        return (bool) data_get($this->conditions, 'requires_kyc', false);
    }

    public function requiresPhone(): bool
    {
        return (bool) data_get($this->conditions, 'requires_phone', false);
    }

    /**
     * @return array<int, string>
     */
    public function allowedCountries(): array
    {
        $countries = (array) data_get($this->conditions, 'countries_allowed', []);

        return array_values(array_filter(array_map('strtoupper', $countries)));
    }
}
