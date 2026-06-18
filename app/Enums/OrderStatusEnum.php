<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case RESERVED = 'reserved';
    case PARTIALLY_RESERVED = 'partially_reserved';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';
}
