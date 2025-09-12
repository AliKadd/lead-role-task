<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('test123'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User A',
            'email' => 'user_a@test.com',
            'password' => bcrypt('test123'),
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'User B',
            'email' => 'user_b@test.com',
            'password' => bcrypt('test123'),
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'User C',
            'email' => 'user_c@test.com',
            'password' => bcrypt('test123'),
            'role' => 'user',
        ]);

        User::factory()->create([
            'name' => 'User D',
            'email' => 'user_d@test.com',
            'password' => bcrypt('test123'),
            'role' => 'user',
        ]);
    }
}
