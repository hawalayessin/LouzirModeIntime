<?php

return [
    [
        'key' => 'sales.carriers.express',
        'name' => 'admin::shipping.express',
        'info' => 'admin::shipping.info',
        'sort' => 3,
        'fields' => [
            ['name' => 'title', 'title' => 'admin::shipping.title', 'type' => 'text', 'validation' => 'max:255', 'channel_based' => true, 'locale_based' => true],
            ['name' => 'description', 'title' => 'admin::shipping.description', 'type' => 'textarea', 'validation' => 'max:2000', 'channel_based' => true, 'locale_based' => true],
            ['name' => 'default_rate', 'title' => 'admin::shipping.rate', 'info' => 'admin::shipping.rate-info', 'type' => 'text', 'validation' => 'required|decimal', 'default' => '8', 'channel_based' => true, 'locale_based' => false],
            ['name' => 'type', 'title' => 'admin::app.configuration.index.sales.shipping-methods.flat-rate-shipping.type.title', 'type' => 'select', 'default' => 'per_order', 'validation' => 'in:per_order,per_unit', 'channel_based' => true, 'locale_based' => false,
                'options' => [
                    ['title' => 'admin::app.configuration.index.sales.shipping-methods.flat-rate-shipping.type.per-order', 'value' => 'per_order'],
                    ['title' => 'admin::app.configuration.index.sales.shipping-methods.flat-rate-shipping.type.per-unit', 'value' => 'per_unit'],
                ],
            ],
            ['name' => 'active', 'title' => 'admin::shipping.active', 'type' => 'boolean', 'default' => '1', 'channel_based' => true, 'locale_based' => false],
        ],
    ],
];
