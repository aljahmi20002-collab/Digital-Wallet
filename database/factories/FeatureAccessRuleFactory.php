<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feature;
use App\Models\FeatureAccessRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeatureAccessRule>
 */
class FeatureAccessRuleFactory extends Factory
{
    protected $model = FeatureAccessRule::class;

    public function definition(): array
    {
        return [
            'feature_id'    => Feature::factory(),
            'panel'         => fake()->randomElement(FeatureAccessRule::PANELS),
            'is_visible'    => true,
            'is_accessible' => true,
            'conditions'    => [],
        ];
    }

    public function forPanel(string $panel): self
    {
        return $this->state(['panel' => $panel]);
    }

    public function hidden(): self
    {
        return $this->state(['is_visible' => false, 'is_accessible' => false]);
    }
}
