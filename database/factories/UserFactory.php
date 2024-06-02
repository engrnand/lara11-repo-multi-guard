<?php

namespace Database\Factories;

use App\Enum\GenderEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'first_name'    => fake()->firstName(),
            'last_name' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'avatar'    => fake()->imageUrl(50, 50),
            'gender'    => fake()->randomElement(array_column(GenderEnum::cases(), 'value')),
            'dob'   => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'two_factor'    => null,
            'notification'  => null,
            'password'  => static::$password ??= Hash::make('secret'),
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),

        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
