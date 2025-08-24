<?php

return [
    'name'       => env('STORE_NAME',    'Boom Center'),
    'address'    => env('STORE_ADDRESS', 'Karang Pawitan'),
    'ig'         => env('STORE_IG',      '@Boom_Center'),
    'city'       => env('STORE_CITY',    'Karawng'),

    // angka
    'tax_rate'   => (float) env('STORE_TAX_RATE', 0),  // 0.10 untuk 10%
    'rounding_to'=> (int)   env('STORE_ROUNDING', 0),  // 500 untuk ke bawah per 500
];
