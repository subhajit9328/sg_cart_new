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
}
