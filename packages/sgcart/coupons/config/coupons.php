<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active Coupon Features
    |--------------------------------------------------------------------------
    | Define which coupon validation features are enabled.
    | Disabling a feature will skip its database columns, hide its admin UI,
    | and bypass its application rules.
    */
    'features' => [
        'min_cart_total' => true,
        'expires_at' => true,
    ],
];
