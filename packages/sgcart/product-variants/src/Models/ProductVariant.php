<?php

namespace SGCart\ProductVariants\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'color_id', 'size_id',
        'sku', 'price', 'sale_price', 'stock', 'min_stock', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function images()
    {
        return $this->hasMany(ProductVariantImage::class, 'product_variant_id');
    }

    public function defaultImage()
    {
        return $this->hasOne(ProductVariantImage::class, 'product_variant_id')->where('is_default', true);
    }

    public function getImageAttribute()
    {
        $default = $this->images->firstWhere('is_default', true);
        return $default ? $default->image_path : ($this->images->first()?->image_path);
    }
}
