<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantFTPDetails;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestaurantFTPDetails>
 */
class RestaurantFTPDetailsFactory extends Factory
{
    protected $model = RestaurantFTPDetails::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(), // Generate a UUID
            'restaurant_id' => Restaurant::factory(), // Link to a Restaurant
            'server' => $this->faker->ipv4, // Random FTP server (IPv4)
            'username' => $this->faker->userName, // Random FTP username
            'password' => $this->faker->password, // Random FTP password
            'port' => $this->faker->numberBetween(21, 65535), // Random FTP port
            'directory' => $this->faker->optional()->word, // Optional directory
            'active' => $this->faker->boolean, // Random active status
            // 'order_column' => $this->faker->optional()->numberBetween(1, 100), // It'll be generated automatically by spatie/eloquent-sortable package
            'updated_by_user_id' => User::factory(), // Assuming you have a User model factory
            'created_by_user_id' => User::factory(),
        ];
    }
}
