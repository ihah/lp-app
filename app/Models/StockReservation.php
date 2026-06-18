<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_id', 'product_id', 'warehouse_id', 'quantity'])]
class StockReservation extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
