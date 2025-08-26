<?php

return [
    'series' => [
        'year' => [
            // 'future' => dari tahun ini ke depan; 'past' => ke belakang
            'direction' => env('DASHBOARD_YEAR_DIR', 'future'),
            // jumlah tahun yang ditampilkan di grafik tahunan
            'span'      => (int) env('DASHBOARD_YEAR_SPAN', 5),
        ],
    ],
];
