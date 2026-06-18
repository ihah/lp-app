<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'on_hand',
        'reserved',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function getAvailableStockAttribute(): int
    {
        return $this->on_hand - $this->reserved;
    }
}
