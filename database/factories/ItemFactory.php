<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(Item::STATUSES);

        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(Item::TYPES),
            'title' => $this->faker->sentence(3),
            'creator' => $this->faker->name(),
            'cover_url' => null,
            'status' => $status,
            'rating' => $status === 'done' ? $this->faker->numberBetween(1, 5) : null,
            'notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'synopsis' => $this->faker->boolean(30) ? $this->faker->paragraph() : null,
            'genre' => $this->faker->randomElement(['Fiction', 'Action', 'Drame', 'RPG', 'Aventure', 'Documentaire']),
            'external_id' => null,
            'external_source' => null,
            'added_at' => now(),
            'finished_at' => $status === 'done' ? now() : null,
        ];
    }
}
