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

        User::factory()->create([
            'name' => 'Shubham Raj',
            'email' => 'shubhambth0000@gmail.com',
            'password' => 'Shubham@123',
        ]);

        $this->call(RestaurantSeeder::class);
        $this->call(RestaurantFTPDetailsSeeder::class);
        $this->call(RestaurantSSHDetailsSeeder::class);
        $this->call(RestaurantDatatbaseDetailsSeeder::class);
    }
}
