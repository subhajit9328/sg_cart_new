<?php

namespace SGCart\Coupons\Models;

use Illuminate\Database\Eloquent\Model;
use SGCart\Coupons\Enums\CouponType;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_cart_total',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        $casts = [
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'is_active' => 'boolean',
        ];

        if (config('coupons.features.min_cart_total', true)) {
            $casts['min_cart_total'] = 'decimal:2';
        }
        if (config('coupons.features.expires_at', true)) {
            $casts['expires_at'] = 'datetime';
        }

        return $casts;
    }

    /**
     * Determine if the coupon is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (config('coupons.features.expires_at', true) && $this->expires_at && Carbon::now()->greaterThan($this->expires_at)) {
            return false;
        }

        return true;
    }
}
