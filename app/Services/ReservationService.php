<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Jobs\RebalanceReservationsJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockReservation;
use App\Models\WarehouseStock;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function reserve(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $productIds = $order->items->pluck('product_id');
            $warehouseStocks = WarehouseStock::whereIn('product_id', $productIds)->lockForUpdate()->get();
            $this->allocateStock($order, $warehouseStocks);
        });
    }

    public function ship(Order $order): Order
    {
        if (!in_array(
            $order->status,
            [OrderStatusEnum::RESERVED, OrderStatusEnum::PARTIALLY_RESERVED]
        )) {
            throw new HttpResponseException(
                response: response()->json([
                    'message' => 'Only reserved and partially reserved orders can be shipped'
                ], 400)
            );
        }

        DB::transaction(function () use ($order) {
            foreach ($order->reservations as $reservation) {
                $this->consume($reservation);
            }

            $order->update([
                'status' => OrderStatusEnum::SHIPPED
            ]);
        });

        return $order;
    }

    public function cancel(Order $order): Order
    {
        if (!in_array($order->status, [
            OrderStatusEnum::PENDING,
            OrderStatusEnum::RESERVED,
            OrderStatusEnum::PARTIALLY_RESERVED,
        ])) {
            throw new HttpResponseException(
                response: response()->json([
                    'message' => 'Only pending, reserved and partially reserved orders can be cancelled'
                ], 400)
            );
        }

        DB::transaction(function () use ($order) {
            $affectedProductIds = [];

            foreach ($order->reservations as $reservation) {
                $affectedProductIds[] = $reservation->product_id;
                $this->release($reservation);
            }

            $order->update([
                'status' => OrderStatusEnum::CANCELLED,
            ]);

            RebalanceReservationsJob::dispatch(
                array_unique($affectedProductIds)
            );
        });

        return $order->refresh();
    }

    public function rebalance(Order $order, array $productIds): void
    {
        DB::transaction(function () use ($order, $productIds) {
            $reservations = $order->reservations()
                ->whereIn('product_id', $productIds)
                ->get();

            OrderItem::whereIn('product_id', $productIds)
                ->where('order_id', $order->id)
                ->update(['missing_quantity' => 0]);

            foreach ($reservations as $reservation) {
                $this->release($reservation);
            }

            $warehouseStocks = WarehouseStock::query()
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get();

            $this->allocateStock($order, $warehouseStocks, $productIds);
        });
    }

    private function consume(StockReservation $reservation)
    {
        $reservation->update([
            'status' => 'consumed'
        ]);

        WarehouseStock::where('warehouse_id', '=', $reservation->warehouse_id)
            ->where('product_id', $reservation->product_id)
            ->decrement('on_hand', $reservation->quantity);

        WarehouseStock::where('warehouse_id', '=', $reservation->warehouse_id)
            ->where('product_id', $reservation->product_id)
            ->decrement('reserved', $reservation->quantity);
    }

    private function release(StockReservation $reservation)
    {
        WarehouseStock::where('warehouse_id', '=', $reservation->warehouse_id)
            ->where('product_id', $reservation->product_id)
            ->decrement('reserved', $reservation->quantity);

        $reservation->delete();
    }

    private function allocateStock(Order $order, Collection $warehouseStocks, ?array $productIds = null): void
    {
        $items = $order->items;

        if ($productIds) {
            $items = $items->whereIn('product_id', $productIds);
        }

        foreach ($items as $item) {
            $needed = $item->requested_quantity;

            $availableStock = $warehouseStocks
                ->where('product_id', $item->product_id)
                ->sortByDesc(function ($row) {
                    return $row->on_hand - $row->reserved;
                });

            foreach ($availableStock as $stock) {
                if ($needed <= 0) {
                    break;
                }

                $available = $stock->on_hand - $stock->reserved;

                if ($available <= 0) {
                    continue;
                }

                $reserve = min($needed, $available);

                StockReservation::create([
                    'order_id' => $order->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $reserve,
                    'status' => 'active',
                ]);

                $stock->increment(
                    'reserved',
                    $reserve
                );

                $needed -= $reserve;
            }

            if ($needed > 0) {

                $item->update([
                    'missing_quantity' => $needed
                ]);
            }
        }

        $order->update([
            'status' => $order->items()->where('missing_quantity', '>', 0)->exists() ? OrderStatusEnum::PARTIALLY_RESERVED : OrderStatusEnum::RESERVED
        ]);
    }
}
