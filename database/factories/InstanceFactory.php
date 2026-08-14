<?php

namespace GamingHub\Core\Database\Factories;

use GamingHub\Core\Models\Instance;
use GamingHub\Core\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\GamingHub\Core\Models\Instance>
 */
class InstanceFactory extends Factory
{
    protected $model = Instance::class;

    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'name' => fake()->unique()->city(),
            'configuration' => [],
        ];
    }
}
