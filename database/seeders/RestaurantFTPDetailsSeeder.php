<?php

namespace Database\Seeders;

use App\Models\RestaurantFTPDetails;
use Illuminate\Database\Seeder;

class RestaurantFTPDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RestaurantFTPDetails::factory()->count(5)->create();
    }
}
