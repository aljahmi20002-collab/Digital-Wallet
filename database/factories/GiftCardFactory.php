<?php

namespace Database\Factories;

use App\Models\GiftCard;
use App\Models\GiftCardTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GiftCard>
 */
class GiftCardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'               => User::factory(),
            'gift_card_template_id' => GiftCardTemplate::factory(),
            'amount'                => $this->faker->randomElement([25, 50, 100, 200]),
            'recipient_name'        => $this->faker->name(),
            'recipient_email'       => $this->faker->safeEmail(),
            'sender_name'           => $this->faker->name(),
            'message'               => $this->faker->sentence(),
            'delivery_method'       => 'email',
            'status'                => 'pending',
            'is_active'             => true,
        ];
    }

    public function redeemed(): static
    {
        return $this->state(fn () => [
            'status'      => 'redeemed',
            'is_active'   => false,
            'redeemed_at' => now(),
            'redeemed_by' => User::factory(),
        ]);
    }
}
