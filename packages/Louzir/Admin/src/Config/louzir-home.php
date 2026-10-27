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
            'type' => in_array($definition['type'], ['image', 'boolean', 'number', 'select', 'color', 'email']) ? $definition['type'] : 'text',
            'validation' => match ($definition['type']) {
                'image' => 'image',
                'email' => 'email|max:254',
                'color' => 'max:7',
                'boolean' => 'boolean',
                'number' => 'integer|min:'.$definition['min'].'|max:'.$definition['max'],
                'select' => 'in:'.implode(',', $definition['options']),
                default => 'max:2000',
            },
            'channel_based' => true,
            'locale_based' => isset($definition['translation']),
        ];

        if (in_array($definition['type'], ['boolean', 'number', 'select', 'color'])) {
            $fields[array_key_last($fields)]['default'] = $definition['fallback'];
        }

        if ($definition['type'] === 'select') {
            $fields[array_key_last($fields)]['options'] = array_map(fn ($value) => [
                'title' => 'admin::louzir-home.'.($definition['option_prefix'] ?? 'sort').'.'.$value,
                'value' => $value,
            ], $definition['options']);
        }
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
