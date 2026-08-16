<?php

namespace Database\Factories;

use App\Models\GiftCardTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GiftCardTemplate>
 */
class GiftCardTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name'           => ucwords($name),
            'category'       => $this->faker->randomElement(GiftCardTemplate::CATEGORIES),
            'preset_key'     => $this->faker->randomElement(GiftCardTemplate::PRESETS),
            'default_amount' => $this->faker->randomElement([25, 50, 100, 200]),
            'status'         => 'active',
            'sort_order'     => $this->faker->numberBetween(1, 100),
        ];
    }
}
