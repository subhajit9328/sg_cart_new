<?php

namespace SGCart\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'cost',
        'min_order_amount',
        'country',
        'state',
        'zip',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate rate cost based on type and subtotal.
     */
    public function calculateCost(float $subtotal): float
    {
        if ($this->type === 'percent') {
            return round(($this->cost / 100) * $subtotal, 2);
        }
        return (float) $this->cost;
    }
}
