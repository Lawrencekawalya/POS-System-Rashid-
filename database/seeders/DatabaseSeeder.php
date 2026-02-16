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
        /**
         * For the AdminUserSeeder, we want to ensure that an admin user is created with a
         * known email and password for testing and initial access purposes. This is especially 
         * important for development and staging environments where you need to log in as an admin 
         * without having to go through a registration process. 
        */
         $this->call(AdminUserSeeder::class);
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
