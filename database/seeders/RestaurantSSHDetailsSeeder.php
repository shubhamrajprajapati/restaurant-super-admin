<?php

namespace Database\Seeders;

use App\Models\RestaurantSSHDetails;
use Illuminate\Database\Seeder;

class RestaurantSSHDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RestaurantSSHDetails::factory()->count(5)->create();
    }
}
