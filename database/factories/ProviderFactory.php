<?php

namespace GamingHub\Core\Database\Factories;

use GamingHub\Core\Models\Provider;
use GamingHub\Core\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\GamingHub\Core\Models\Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'connector_instance_id' => null,
            'config' => [],
            'status' => fake()->randomElement(['connected', 'disconnected', 'error']),
        ];
    }
}
