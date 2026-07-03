<?php

namespace SGCart\Marketplace\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Models\Product;

class Seller extends Authenticatable
{
    use SoftDeletes, HasUlids;

    protected $fillable = [
        'ulid',
        'name',
        'email',
        'password',
        'phone_no',
        'shop_name',
        'shop_slug',
        'shop_description',
        'address',
        'status',
        'suspension_reason',
        'commission_rate',
        'bank_details',
        'approved_at',
        'approved_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'commission_rate' => 'decimal:2',
            'status' => \App\Enums\SellerStatus::class,
        ];
    }

    /**
     * Specify unique columns that require ULID generation.
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * Use the custom 'ulid' column for route model binding.
     */
    public function getRouteKeyName()
    {
        return 'ulid';
    }

    /**
     * Relationship to products owned by the seller.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Helper to retrieve active commission rate.
     */
    public function getActiveCommissionRate(): float
    {
        return (float) ($this->commission_rate ?? config('marketplace.default_commission_rate', 10.00));
    }
}
