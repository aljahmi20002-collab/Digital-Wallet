<?php

namespace Database\Seeders;

use App\Models\GiftCardTemplate;
use Illuminate\Database\Seeder;

class GiftCardTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['name' => 'Confetti Pop',   'category' => 'Birthday',         'preset_key' => 'birthday',    'ribbon_text' => 'Happy Birthday',     'sort_order' => 1],
            ['name' => 'Rose Garden',    'category' => 'Birthday',         'preset_key' => 'anniversary', 'ribbon_text' => 'Happy Birthday',     'sort_order' => 2],
            ['name' => 'Pine & Lights',  'category' => 'Holiday',          'preset_key' => 'holiday',     'ribbon_text' => "Season's Greetings", 'sort_order' => 3],
            ['name' => 'Midnight Frost', 'category' => 'Holiday',          'preset_key' => 'premium',     'ribbon_text' => "Season's Greetings", 'sort_order' => 4],
            ['name' => 'Golden Hour',    'category' => 'Thank You',        'preset_key' => 'thankyou',    'ribbon_text' => 'With Gratitude',     'sort_order' => 5],
            ['name' => 'Quiet Thanks',   'category' => 'Thank You',        'preset_key' => 'premium',     'ribbon_text' => 'With Gratitude',     'sort_order' => 6],
            ['name' => 'Eternal Plum',   'category' => 'Anniversary',      'preset_key' => 'anniversary', 'ribbon_text' => 'Happy Anniversary',  'sort_order' => 7],
            ['name' => 'Sky Sparkle',    'category' => 'Congratulations',  'preset_key' => 'congrats',    'ribbon_text' => 'Congratulations',    'sort_order' => 8],
            ['name' => 'Sun Burst',      'category' => 'Congratulations',  'preset_key' => 'thankyou',    'ribbon_text' => 'Congratulations',    'sort_order' => 9],
            ['name' => 'Navy Classic',   'category' => 'General',          'preset_key' => 'premium',     'ribbon_text' => 'A Gift For You',     'sort_order' => 10],
            ['name' => 'Evergreen',      'category' => 'General',          'preset_key' => 'holiday',     'ribbon_text' => 'A Gift For You',     'sort_order' => 11],
        ];

        foreach ($templates as $row) {
            GiftCardTemplate::updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['status' => 'active']),
            );
        }
    }
}
