<?php

use Webkul\Shop\Helpers\HomeContent;

$sections = [
    [
        'key' => 'general.louzir_home',
        'name' => 'admin::louzir-home.title',
        'info' => 'admin::louzir-home.info',
        'icon' => 'settings/store.svg',
        'sort' => 3,
    ],
];

foreach (HomeContent::schema() as $group => $definitions) {
    $fields = [];

    foreach ($definitions as $name => $definition) {
        $fields[] = [
            'name' => $name,
            'title' => 'admin::louzir-home.fields.'.$name,
            'type' => $definition['type'] === 'image' ? 'image' : 'text',
            'validation' => $definition['type'] === 'image'
                ? 'image'
                : 'max:2000',
            'channel_based' => true,
            'locale_based' => false,
        ];
    }

    $sections[] = [
        'key' => 'general.louzir_home.'.$group,
        'name' => 'admin::louzir-home.sections.'.$group,
        'info' => 'admin::louzir-home.info',
        'sort' => count($sections),
        'fields' => $fields,
    ];
}

return $sections;
