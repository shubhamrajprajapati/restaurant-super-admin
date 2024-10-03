<?php

namespace Database\Seeders;

use App\Models\RestaurantDatatbaseDetails;
use Illuminate\Database\Seeder;

class RestaurantDatatbaseDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RestaurantDatatbaseDetails::factory()->count(5)->create();
    }
}
