<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['status'])]
class Order extends Model
{
    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    public function reservations()
    {
        return $this->hasMany(StockReservation::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
