<?php

namespace SGCart\SocialShare\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

class SocialPost extends Model
{
    protected $fillable = [
        'customer_id',
        'media_path',
        'media_type',
        'order_number',
        'product_sku',
        'product_id',
        'caption',
        'shop_link',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /**
     * Get the customer (influencer) who created the post.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the associated product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the admin who approved the post.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to only include approved posts.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
