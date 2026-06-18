<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShipOrderRequest;
use App\Models\Order;
use App\Services\ReservationService;

class ShipOrderController extends Controller
{
    public function __invoke(ShipOrderRequest $request, ReservationService $service)
    {
        $order = Order::query()
            ->where('id', $request->validated('id'))
            ->with('items')
            ->firstOrFail();

        return $service->ship($order);
    }
}
