<?php

namespace SGCart\Reviews\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Product;

class Review extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'rating',
        'comment',
        'status_id',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rating' => 'integer',
        'status_id' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function status()
    {
        return $this->belongsTo(ReviewStatus::class, 'status_id');
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class);
    }
}
