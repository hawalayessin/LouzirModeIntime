<?php

use Webkul\Shipping\Carriers\Express;

return [
    'flatrate' => [
        'code' => 'flatrate',
        'title' => 'Livraison normale',
        'description' => 'Livraison normale à domicile',
        'active' => true,
        'default_rate' => '8',
        'type' => 'per_order',
        'class' => 'Webkul\Shipping\Carriers\FlatRate',
    ],

    'express' => [
        'code' => 'express',
        'title' => 'Livraison express',
        'description' => 'Livraison express à domicile',
        'active' => true,
        'default_rate' => '8',
        'type' => 'per_order',
        'class' => Express::class,
    ],

    'free' => [
        'code' => 'free',
        'title' => 'Free Shipping',
        'description' => 'Free Shipping',
        'active' => false,
        'default_rate' => '0',
        'class' => 'Webkul\Shipping\Carriers\Free',
    ],
];
