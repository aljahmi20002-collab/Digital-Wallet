<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int                                $id
 * @property string                             $key
 * @property string                             $label
 * @property string                             $category
 * @property string|null                        $description
 * @property string|null                        $icon
 * @property bool                               $is_enabled
 * @property bool                               $is_core
 * @property array|null                         $meta
 * @property int                                $sort_order
 * @property Collection<int, FeatureAccessRule> $accessRules
 */
class Feature extends Model
{
    /** @use HasFactory<FeatureFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'category',
        'description',
        'icon',
        'is_enabled',
        'is_core',
        'meta',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_core'    => 'boolean',
            'meta'       => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<FeatureAccessRule>
     */
    public function accessRules(): HasMany
    {
        return $this->hasMany(FeatureAccessRule::class);
    }

    /**
     * Fetch (or lazily create) the access rule for a given panel.
     */
    public function ruleFor(string $panel): FeatureAccessRule
    {
        return $this->accessRules
            ->firstWhere('panel', $panel)
            ?? $this->accessRules()->create([
                'panel'         => $panel,
                'is_visible'    => true,
                'is_accessible' => true,
                'conditions'    => [],
            ]);
    }
}
