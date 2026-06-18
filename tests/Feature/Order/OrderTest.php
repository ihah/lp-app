<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_store_reserves_stock()
    {
        $this->seed();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 5],
            ]
        ]);

        $this->assertDatabaseHas('orders', [
            'status' => OrderStatusEnum::RESERVED->value
        ]);

        $this->assertDatabaseHas('stock_reservations', [
            'product_id' => 1,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'product_id' => 1,
            'reserved' => 5,
        ]);
    }


    public function test_cancel_releases_stock()
    {
        $this->seed();

        // reserve stock first
        $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 5],
            ]
        ]);

        $order = Order::with('reservations')->first();
        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatusEnum::CANCELLED->value
        ]);

        $this->assertDatabaseMissing('stock_reservations', [
            'id' => $order->reservations->first()->id,
        ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'product_id' => 1,
            'reserved' => 0,
        ]);
    }

    public function test_order_ship()
    {
        $warehouses = $this->createWarehouses(2);

        $this->createProductsWithStock($warehouses, 1, [50, 20]);
        $this->createProductsWithStock($warehouses, 1, [60, 15]);

        $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 40],
                ['id' => 2, 'quantity' => 55],
            ]
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'status' => OrderStatusEnum::RESERVED->value
            ]);

        $this->postJson('/api/orders/ship', ['id' => 1])->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => 1,
            'status' => OrderStatusEnum::SHIPPED->value
        ]);

        $this->deleteJson('/api/orders/1')->assertStatus(400);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => 1,
            'product_id' => 1,
            'on_hand' => 10,
            'reserved' => 0,
        ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => 1,
            'product_id' => 2,
            'on_hand' => 5,
            'reserved' => 0,
        ]);

        $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 15],
                ['id' => 2, 'quantity' => 18],
            ]
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'status' => OrderStatusEnum::RESERVED->value
            ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => 1,
            'product_id' => 1,
            'on_hand' => 10,
            'reserved' => 0,
        ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => 1,
            'product_id' => 2,
            'on_hand' => 5,
            'reserved' => 3,
        ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => 2,
            'product_id' => 1,
            'on_hand' => 20,
            'reserved' => 15,
        ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => 2,
            'product_id' => 2,
            'on_hand' => 15,
            'reserved' => 15,
        ]);
    }

    public function test_cant_reship_order()
    {
        $warehouses = $this->createWarehouses(2);
        $this->createProductsWithStock($warehouses, 1, [50, 20]);

        $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 40],
            ]
        ])->assertCreated();

        $this->postJson('/api/orders/ship', ['id' => 1])->assertStatus(200);

        // should fail
        $this->postJson('/api/orders/ship', ['id' => 1])->assertStatus(400);
    }

    public function test_cant_cancel_shipped()
    {
        $warehouses = $this->createWarehouses(2);

        $this->createProductsWithStock($warehouses, 1, [50, 20]);

        $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 40],
            ]
        ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'status' => OrderStatusEnum::RESERVED->value
            ]);

        $this->postJson('/api/orders/ship', ['id' => 1])->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => 1,
            'status' => OrderStatusEnum::SHIPPED->value
        ]);

        $this->deleteJson('/api/orders/1')->assertStatus(400);
    }

    public function test_rebalance_stock()
    {
        $warehouses = $this->createWarehouses(2);

        $this->createProductsWithStock($warehouses, 1, [50, 20]);
        $this->createProductsWithStock($warehouses, 1, [60, 15]);

        // created two orders, first one reserves all stock from the first warehouse
        // second one is partially reserved
        $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 55],
            ]
        ]);

        $this->postJson('/api/orders', [
            'items' => [
                ['id' => 1, 'quantity' => 20],
            ]
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => 1,
            'status' => OrderStatusEnum::RESERVED->value
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => 2,
            'status' => OrderStatusEnum::PARTIALLY_RESERVED->value
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => 2,
            'product_id' => 1,
            'missing_quantity' => 5,
        ]);

        // cancel first order
        // it will rebalance stock to make second order fully reserved from the first warehouse
        $this->deleteJson('/api/orders/1');

        $this->assertDatabaseHas('orders', [
            'id' => 1,
            'status' => OrderStatusEnum::CANCELLED->value
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => 2,
            'status' => OrderStatusEnum::RESERVED->value
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => 2,
            'product_id' => 1,
            'missing_quantity' => 0,
        ]);

        $this->assertDatabaseHas('stock_reservations', [
            'order_id' => 2,
            'product_id' => 1,
            'warehouse_id' => 1,
            'quantity' => 20,
        ]);
    }
}
