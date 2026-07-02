<?php

namespace App\Enums;

enum OrderStatus: string
{
    case NEW_ORDER = 'New Order';
    case PROCESSED = 'Processed';
    case SHIPPED = 'Shipped';
    case OUT_FOR_DELIVERY = 'Out for Delivery';
    case DELIVERED = 'Delivered';
    case CANCELLED = 'Cancelled';
    case PROCESSING = 'Processing'; // Legacy support
}
