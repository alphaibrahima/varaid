<?php

return [
    'phone_codes' => [
        'fr' => [
            'code' => '33',
            'name' => 'France',
            'pattern' => '/^33\d{9}$/',
            'mask' => '99 99 99 99 99',
            'example' => '6 12 34 56 78'
        ],
        'sn' => [
            'code' => '221',
            'name' => 'Sénégal',
            'pattern' => '/^221\d{9}$/',
            'mask' => '99 999 99 99',
            'example' => '77 123 45 67'
        ],
        'gn' => [
            'code' => '224',
            'name' => 'Guinée',
            'pattern' => '/^224\d{8}$/',
            'mask' => '999 999 99',
            'example' => '624 12 34 56'
        ],
        // Ajouter d'autres pays selon besoin
    ]
];