<?php

namespace Database\Factories;

use App\Models\PhoneVerificationCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<PhoneVerificationCode>
 */
class PhoneVerificationCodeFactory extends Factory
{
    protected $model = PhoneVerificationCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'phone_number' => '+1555'.fake()->numerify('#######'),
            'code_hash'    => Hash::make('123456'),
            'attempts'     => 0,
            'sent_at'      => now(),
            'expires_at'   => now()->addMinutes(10),
            'verified_at'  => null,
        ];
    }
}
