<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ReservationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RebalanceReservationsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private array $productIds
    ) {}

    public function handle(ReservationService $service): void
    {
        $orders = Order::query()
            ->whereIn('status', [
                'reserved',
                'partially_reserved',
            ])
            ->whereHas('items', function ($query) {
                $query->whereIn('product_id', $this->productIds);
            })
            ->get();

        foreach ($orders as $order) {
            $service->rebalance($order, $this->productIds);
        }
    }
}
