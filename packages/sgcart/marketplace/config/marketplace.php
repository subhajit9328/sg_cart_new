<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Commission Rate
    |--------------------------------------------------------------------------
    |
    | This value represents the default commission percentage charged by the
    | platform for every vendor sale. Can be overridden per seller in
    | the database 'sellers' table.
    |
    */
    'default_commission_rate' => 10.00, // 10%

    /*
    |--------------------------------------------------------------------------
    | Supported Banks List
    |--------------------------------------------------------------------------
    |
    | A list of bank names available to sellers when configuring their payout
    | bank details.
    |
    */
    'banks' => [
        'State Bank of India',
        'HDFC Bank',
        'ICICI Bank',
        'Axis Bank',
        'Kotak Mahindra Bank',
        'IndusInd Bank',
        'Yes Bank',
        'Punjab National Bank',
        'Bank of Baroda',
        'Union Bank of India',
        'Canara Bank',
        'IDBI Bank',
        'Federal Bank',
        'Standard Chartered',
        'HSBC Bank',
        'Citibank',
        'Deutsche Bank',
        'DBS Bank',
        'Other',
    ],
];
