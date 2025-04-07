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
        User::factory()->create([
            'name' => ' User',
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'User1',
            'email' => 'user2@example.com',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Test User 3',
            'email' => 'test3@example.com',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Test User 4',
            'email' => 'test4@example.com',
            'password' => 'password',
        ]);
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
