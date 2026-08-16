<?php

namespace App\Models;

use App\Enums\ComponentType;
use App\Enums\Theme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PageComponent extends Model
{
    use HasFactory;

    protected $table = 'page_components';

    protected $fillable = [
        'component_icon',
        'component_name',
        'component_key',
        'content_data',
        'type',
        'theme',
        'sort',
        'repeated_content',
        'is_active',
    ];

    protected $casts = [
        'content_data'     => 'array',
        'type'             => ComponentType::class,
        'theme'            => Theme::class,
        'repeated_content' => 'boolean',
        'is_protected'     => 'boolean',
        'is_active'        => 'boolean',
    ];

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Restrict the query to components belonging to a given theme.
     * Used by the page builder to show only the active theme's blocks.
     */
    public function scopeForTheme($query, Theme|string|null $theme)
    {
        if ($theme === null) {
            return $query;
        }

        $value = $theme instanceof Theme ? $theme->value : (string) $theme;

        return $query->where('theme', $value);
    }

    /*
     |--------------------------------------------------------------------------
     | Accessors
     |--------------------------------------------------------------------------
     */
    public function getSectionNameAttribute(): string
    {
        if ($this->type === ComponentType::Dynamic) {
            return $this->type->value;
        }

        return $this->component_key;
    }

    public function getResolvedComponentIconAttribute(): ?string
    {
        $iconCandidates = [];

        if (is_string($this->component_icon) && $this->component_icon !== '') {
            $iconCandidates[] = 'general/static/component/'.pathinfo($this->component_icon, PATHINFO_FILENAME).'.svg';
        }

        if (is_string($this->component_key) && $this->component_key !== '') {
            $normalizedComponentKey = str_replace('_', '-', strtolower($this->component_key));

            $iconCandidates[] = "general/static/component/{$normalizedComponentKey}.svg";
            $iconCandidates[] = 'general/static/component/'.strtolower($this->component_key).'.svg';
        }

        foreach (array_unique($iconCandidates) as $iconCandidate) {
            if (file_exists(public_path($iconCandidate))) {
                return $iconCandidate;
            }
        }

        $defaultIcon = 'general/static/component/component-default.svg';

        if (
            (
                ! is_string($this->component_icon)
                || $this->component_icon === ''
                || str_starts_with($this->component_icon, 'general/static/component/')
            )
            && file_exists(public_path($defaultIcon))
        ) {
            return $defaultIcon;
        }

        return $this->component_icon;
    }

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function repeatedContents()
    {
        return $this->hasMany(PageComponentRepeatedContent::class, 'component_id');
    }

    public function limitRepeatedContentsOver(): bool
    {
        if (! $this->component_key) {
            return false;
        }

        $componentKey = strtolower($this->component_key);
        $cacheKey     = "component_definition_{$componentKey}";

        $definition = cache()->rememberForever($cacheKey, function () use ($componentKey) {
            $file = resource_path("structure/page_component/{$componentKey}.php");

            return file_exists($file) ? include $file : [];
        });

        $limit = $definition['repeated_content_limit'] ?? null;

        return is_numeric($limit) && $limit > 0 && $this->repeatedContents()->count() >= (int) $limit;
    }

    /*
     |--------------------------------------------------------------------------
     | Helper Methods
     |--------------------------------------------------------------------------
     */

    /**
     * Resolve the schema definition for a component key, scoped to a theme.
     *
     * Lookup order:
     *   1. `resources/structure/page_component/{theme}/{name}.php`  (theme-specific)
     *   2. `resources/structure/page_component/{name}.php`          (legacy / classic fallback)
     *
     * The fallback keeps existing classic components untouched: their schema
     * files live at the flat root path and continue to load when no theme
     * argument is provided or when the themed file doesn't exist.
     */
    public static function contentFields($name, $type = 'component_fields', Theme|string|null $theme = null): array
    {
        $name = strtolower((string) $name);

        $themeSlug = $theme instanceof Theme
            ? $theme->value
            : (is_string($theme) && $theme !== '' ? strtolower($theme) : null);

        $candidates = [];

        if ($themeSlug !== null) {
            $candidates[] = resource_path("structure/page_component/{$themeSlug}/{$name}.php");
        }
        $candidates[] = resource_path("structure/page_component/{$name}.php");

        foreach ($candidates as $file) {
            if (file_exists($file)) {
                $definition = include $file;

                return $definition[$type] ?? (is_array($definition) ? $definition : []);
            }
        }

        \Log::warning("Component structure not found for key '{$name}' (theme: ".($themeSlug ?? 'none').')');

        return [];
    }

    /*
     |--------------------------------------------------------------------------
     | Model Events - Auto Cache Clear
     |--------------------------------------------------------------------------
     */

    protected static function booted()
    {
        static::saved(fn (PageComponent $component) => $component->flushRelatedPagesCache());
        static::deleted(fn (PageComponent $component) => $component->flushRelatedPagesCache());
    }

    /**
     * Flush cache of all Pages where this component is used.
     */
    public function flushRelatedPagesCache(): void
    {
        $pages = Page::whereJsonContains('component_ids', (string) $this->id)->pluck('id', 'slug');

        foreach ($pages as $slug => $pageId) {
            Cache::forget("page_components_{$pageId}");
            Cache::forget('page_slug_'.md5($slug));
        }
    }
}
