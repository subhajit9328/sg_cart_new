<?php

namespace SGCart\ProductVariants\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantImage extends Model
{
    protected $table = 'product_variant_images';

    protected $fillable = ['product_variant_id', 'image_path', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
