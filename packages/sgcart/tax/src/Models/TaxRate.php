<?php

namespace SGCart\Tax\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'rate',
        'country',
        'state',
        'zip',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate tax cost based on type and subtotal.
     */
    public function calculateTax(float $subtotal): float
    {
        if ($this->type === 'flat') {
            return (float) $this->rate;
        }
        return round(($this->rate / 100) * $subtotal, 2);
    }
}
