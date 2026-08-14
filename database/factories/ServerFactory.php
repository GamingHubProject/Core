<?php

namespace GamingHub\Core\Database\Factories;

use GamingHub\Core\Models\Game;
use GamingHub\Core\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\GamingHub\Core\Models\Server>
 */
class ServerFactory extends Factory
{
    protected $model = Server::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'game_id' => Game::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['online', 'offline', 'maintenance']),
            'max_players' => fake()->numberBetween(10, 200),
            'current_players' => fake()->numberBetween(0, 10),
        ];
    }
}
