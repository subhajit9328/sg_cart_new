<?php

namespace SGCart\ProductVariants\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = ['name', 'code'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
