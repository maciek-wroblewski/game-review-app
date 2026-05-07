<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'username' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'), // log in with email: admin@test.com, pw: password
            'verified' => true,
        ]);

        User::factory(20)->create();
    }
}
