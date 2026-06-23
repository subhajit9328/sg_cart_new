<?php

namespace SGCart\ProductVariants\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = ['name', 'hex_code'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
