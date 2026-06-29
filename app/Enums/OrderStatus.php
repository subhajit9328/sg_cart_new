<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PROCESSING = 'Processing';
    case DELIVERED = 'Delivered';
    case SHIPPED = 'Shipped';
    case CANCELLED = 'Cancelled';
}
