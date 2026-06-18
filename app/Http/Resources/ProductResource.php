<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'total_stock' => $this->total_stock,
            'available_stock' => $this->whenLoaded('warehouseStocks', function () {
                return $this->warehouseStocks->sum(fn($stock) => $stock->available_stock);
            }),
            'warehouse_stocks' => $this->whenLoaded('warehouseStocks', function () {
                return $this->warehouseStocks->map(fn($stock) => [
                    'id' => $stock->id,
                    'warehouse_id' => $stock->warehouse_id,
                    'available' => $stock->available_stock,
                    'on_hand' => $stock->on_hand,
                    'reserved' => $stock->reserved,
                ]);
            }),
        ];
    }
}
