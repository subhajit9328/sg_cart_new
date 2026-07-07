<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Approval Requirement
    |--------------------------------------------------------------------------
    |
    | If set to true, newly submitted reviews will have a default status of
    | "Pending" and require approval before showing on the storefront.
    | If set to false, reviews will automatically be set to "Approved".
    |
    */
    'admin_approval' => true,

    /*
    |--------------------------------------------------------------------------
    | Maximum Images Per Review
    |--------------------------------------------------------------------------
    |
    | Defines the maximum number of images a customer can upload with their review.
    | Set to 0 to disable image uploads entirely.
    |
    */
    'max_no_image' => 5,
];
