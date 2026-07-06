<?php

namespace SGCart\ProductVariants\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = ['name', 'hex_code', 'seller_id'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function seller()
    {
        return $this->belongsTo(\SGCart\Marketplace\Models\Seller::class, 'seller_id');
    }

    /**
     * Get the color's hex code, ensuring it starts with #.
     */
    public function getHexCodeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        return str_starts_with($value, '#') ? $value : '#' . $value;
    }

    /**
     * Set the color's hex code, ensuring it starts with #.
     */
    public function setHexCodeAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['hex_code'] = str_starts_with($value, '#') ? $value : '#' . $value;
        } else {
            $this->attributes['hex_code'] = $value;
        }
    }
}
