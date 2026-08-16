<?php

namespace Database\Factories;

use App\Models\MobileRechargeProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MobileRechargeProvider>
 */
class MobileRechargeProviderFactory extends Factory
{
    protected $model = MobileRechargeProvider::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->slug(2);

        return [
            'plugin_id'            => null,
            'code'                 => $code,
            'name'                 => ucwords(str_replace('-', ' ', $code)),
            'driver'               => 'sandbox',
            'logo'                 => null,
            'description'          => fake()->sentence(),
            'status'               => true,
            'is_default'           => false,
            'supported_countries'  => null,
            'supported_currencies' => null,
            'fee_fixed'            => 0,
            'fee_percent'          => 0,
            'min_amount'           => 10,
            'max_amount'           => 10000,
            'config'               => [],
            'order'                => 0,
        ];
    }

    public function default(): self
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function reloadly(): self
    {
        return $this->state(fn () => [
            'driver' => 'reloadly',
            'config' => [
                'sandbox'         => true,
                'default_country' => 'BD',
            ],
        ]);
    }
}
