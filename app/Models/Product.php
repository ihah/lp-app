<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[Fillable(['name', 'sku'])]
class Product extends Model
{
    use HasFactory;

    public function reservations()
    {
        return $this->hasMany(StockReservation::class);
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function getTotalStockAttribute(): int
    {
        $this->loadMissing('warehouseStocks');
        return $this->warehouseStocks->sum(fn($stock) => $stock->on_hand);
    }
}
