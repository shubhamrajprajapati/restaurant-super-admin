<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantDatatbaseDetails;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestaurantDatatbaseDetails>
 */
class RestaurantDatatbaseDetailsFactory extends Factory
{
    protected $model = RestaurantDatatbaseDetails::class;

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

            'host' => $this->faker->ipv4, // Random SSH host (IPv4)
            'port' => $this->faker->numberBetween(1, 65535), // Random SSH port
            'database' => $this->faker->word,
            'username' => $this->faker->userName, // Random SSH username
            'password' => $this->faker->password, // Random SSH password
            'active' => $this->faker->boolean, // Random active status
            // 'order_column' => $this->faker->optional()->numberBetween(1, 100), // It'll be generated automatically by spatie/eloquent-sortable package
            'updated_by_user_id' => User::factory(), // Assuming you have a User model factory
            'created_by_user_id' => User::factory(),
        ];
    }
}
