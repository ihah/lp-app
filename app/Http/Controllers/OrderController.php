<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\ReservationService;

class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, ReservationService $service)
    {
        $items = $request->validated('items', []);

        $order = Order::create([
            'status' => OrderStatusEnum::PENDING
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['id'],
                'requested_quantity' => $item['quantity']
            ]);
        }

        $service->reserve($order);

        return new OrderResource($order->loadMissing('items'));
    }

    public function destroy(Order $order, ReservationService $service)
    {
        $order->load('items');
        return $service->cancel($order);
    }
}
