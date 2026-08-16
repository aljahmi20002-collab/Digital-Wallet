<?php

namespace App\Models;

use Database\Factories\GiftCardTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GiftCardTemplate extends Model
{
    /** @use HasFactory<GiftCardTemplateFactory> */
    use HasFactory;

    public const array PRESETS = ['birthday', 'holiday', 'thankyou', 'anniversary', 'congrats', 'premium'];

    public const array CATEGORIES = ['Birthday', 'Holiday', 'Thank You', 'Anniversary', 'Congratulations', 'General'];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'preset_key',
        'background_color',
        'text_color',
        'ribbon_text',
        'default_amount',
        'image',
        'thumbnail',
        'status',
        'sort_order',
        'used_count',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'float',
            'sort_order'     => 'integer',
            'used_count'     => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $template) {
            if (! $template->slug) {
                $base = Str::slug($template->name);
                $slug = $base;
                $i    = 1;
                while (self::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $template->slug = $slug;
            }
        });
    }

    public function giftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
