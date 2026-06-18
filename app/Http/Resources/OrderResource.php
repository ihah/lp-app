<?php

namespace App\Http\Resources;

use App\Enums\OrderStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'missing_items' => $this->when(
                $this->status == OrderStatusEnum::PARTIALLY_RESERVED,
                $this->whenLoaded('items', function () {
                    return $this->items
                        ->filter(fn($item) => $item->missing_quantity > 0)
                        ->map(fn($item) => [
                            'id' => $item->product_id,
                            'order_id' => $item->order_id,
                            'requested_quantity' => $item->requested_quantity,
                            'missing_quantity' => $item->missing_quantity
                        ]);
                })
            ),
        ];
    }
}
