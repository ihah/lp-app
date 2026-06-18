<?php

namespace Tests;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function createWarehouses($count = 1)
    {
        return Warehouse::factory()->count($count)->create();
    }

    public function createProductsWithStock(Collection $warehouses, int $count = 1, array $stockData = [])
    {
        $products = Product::factory()->count($count)->create();

        foreach ($warehouses as $index => $warehouse) {
            foreach ($products as $product) {
                WarehouseStock::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'on_hand' => Arr::get($stockData, $index, 0),
                    'reserved' => 0,
                ]);
            }
        }
    }
}
