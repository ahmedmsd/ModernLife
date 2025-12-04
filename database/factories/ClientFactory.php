<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'client_name' => fake()->company(),
            'client_email' => fake()->unique()->safeEmail(),
            'client_phone' => fake()->phoneNumber(),
        ];
    }
}

