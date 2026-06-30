<?php

namespace SGCart\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\User;

class InventoryLog extends Model
{
    public $timestamps = false; // We use created_at only (timestamp)
    
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'quantity',
        'action',
        'reason',
        'user_id',
        'before_stock',
        'after_stock',
        'created_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        if (class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)) {
            return $this->belongsTo(\SGCart\ProductVariants\Models\ProductVariant::class, 'product_variant_id');
        }
        
        // Graceful fallback relation if variants package is not present
        return $this->belongsTo(Product::class, 'product_id')->whereRaw('1 = 0');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
