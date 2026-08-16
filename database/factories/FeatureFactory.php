<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        $label = fake()->unique()->words(2, true);

        return [
            'key'         => Str::snake($label).'_'.fake()->unique()->numberBetween(1, 9999),
            'label'       => Str::title($label),
            'category'    => fake()->randomElement(['money_movement', 'p2p', 'business', 'cards', 'engagement']),
            'description' => fake()->sentence(),
            'icon'        => null,
            'is_enabled'  => true,
            'is_core'     => false,
            'meta'        => [],
            'sort_order'  => fake()->numberBetween(0, 99),
        ];
    }

    public function core(): self
    {
        return $this->state(['is_core' => true]);
    }

    public function disabled(): self
    {
        return $this->state(['is_enabled' => false]);
    }
}
