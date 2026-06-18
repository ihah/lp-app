<?php

namespace Database\Factories;

use App\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseStock>
 */
class WarehouseStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'on_hand' => fake()->numberBetween(0, 100),
            'reserved' => 0,
        ];
    }
}
