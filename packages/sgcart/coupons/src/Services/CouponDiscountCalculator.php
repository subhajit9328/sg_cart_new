<?php

namespace SGCart\Coupons\Services;

use SGCart\Coupons\Models\Coupon;
use SGCart\Coupons\Enums\CouponType;

class CouponDiscountCalculator
{
    /**
     * Calculate the discount amount for a given coupon code and subtotal.
     */
    public function calculate(?string $code, float $subtotal): float
    {
        if (empty($code) || $subtotal <= 0) {
            return 0.0;
        }

        // Fetch coupon by code
        $coupon = Coupon::where('code', trim($code))->first();

        // Validate coupon availability
        if (!$coupon || !$coupon->isValid()) {
            return 0.0;
        }

        // Validate minimum cart requirement
        if ($subtotal < (float) $coupon->min_cart_total) {
            return 0.0;
        }

        // Calculate based on enum type
        switch ($coupon->type) {
            case CouponType::FLAT:
                $discount = (float) $coupon->value;
                break;
            case CouponType::PERCENT:
                $discount = $subtotal * ((float) $coupon->value / 100.0);
                break;
            default:
                $discount = 0.0;
        }

        // Cap the discount to not exceed the subtotal
        return min($discount, $subtotal);
    }
}
