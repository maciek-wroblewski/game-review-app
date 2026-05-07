<?php

namespace Database\Factories;

use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'comments' => fake()->randomElement(['everyone', 'mutuals', 'nobody']),
            'dms' => fake()->randomElement(['everyone', 'mutuals', 'nobody']),
        ];
    }
}