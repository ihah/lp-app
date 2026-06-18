<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $warehouses = Warehouse::factory()->count(5)->create();
        $products = Product::factory()->count(25)->create();

        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                WarehouseStock::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'on_hand' => rand(10, 100),
                    'reserved' => 0,
                ]);
            }
        }
    }
}
