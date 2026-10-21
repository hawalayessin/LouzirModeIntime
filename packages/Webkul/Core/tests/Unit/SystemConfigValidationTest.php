<?php

use Webkul\Core\SystemConfig\Item;

it('maps configuration validators without mutating the server rules', function ($type, $server, $client) {
    $section = new Item(
        children: collect(),
        fields: [['name' => 'test', 'title' => 'Test', 'type' => $type, 'validation' => $server]],
        icon: null,
        icon_class: null,
        info: null,
        key: 'general.test',
        name: 'Test',
    );
    $field = $section->getFields()->first();

    foreach (range(1, 4) as $read) {
        expect($field->getValidations())->toBe($client);
        $field->isRequired();
        $field->toArray();
    }

    expect($field->validation)->toBe($server)
        ->and(json_decode(json_encode($field), true)['validation'])->toBe($server);
})->with([
    ['number', 'required|integer|min:10|max:120', 'required|integer|min_value:10|max_value:120'],
    ['select', 'in:created_at-desc,name-asc,price-asc,price-desc', 'one_of:created_at-desc,name-asc,price-asc,price-desc'],
    ['multiselect', 'in:a,b', 'one_of:a,b'],
    ['text', 'max:2000', 'max:2000'],
    ['text', 'in:admin,minimum,maximum', 'in:admin,minimum,maximum'],
]);
