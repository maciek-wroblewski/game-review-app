<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            // We use static password so all test users have the same password (e.g., 'password')
            'password' => static::$password ??= Hash::make('password'),
            'bio' => fake()->sentence(),
            // We can use a service like DiceBear to generate quick random avatars
            'avatar' => 'https://api.dicebear.com/7.x/pixel-art/svg?seed=' . fake()->word(),
            'banner' => 'https://picsum.photos/seed/' . fake()->uuid() . '/1200/400',
            'verified' => fake()->boolean(10), // 10% chance to be a verified user
            'remember_token' => Str::random(10),
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
