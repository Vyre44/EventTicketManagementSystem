<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        // Organizer
        User::factory()->create([
            'name' => 'Organizer User',
            'email' => 'organizer@example.com',
            'role' => 'organizer',
            'password' => bcrypt('organizer123'),
        ]);

        // Attendee
        User::factory()->create([
            'name' => 'Attendee User',
            'email' => 'attendee@example.com',
            'role' => 'attendee',
            'password' => bcrypt('attendee123'),
        ]);
    }
}
