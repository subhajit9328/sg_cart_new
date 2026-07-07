<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Related Products
    |--------------------------------------------------------------------------
    |
    | Maximum number of related products to display on the product detail page.
    | The fallback resolver fills this quota sequentially:
    |   1st — Name matching
    |   2nd — Category matching  (fills remaining slots)
    |   3rd — Search tag matching (fills any still-remaining slots)
    |
    */

    'max_no_related_products' => env('MAX_NO_RELATED_PRODUCTS', 10),

];
