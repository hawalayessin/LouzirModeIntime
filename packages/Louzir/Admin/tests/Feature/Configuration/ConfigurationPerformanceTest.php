<?php

use Illuminate\Support\Facades\DB;
use Webkul\Shop\Helpers\HomeContent;

it('reads home configuration once per section and skips unchanged database writes', function () {
    $this->loginAsAdmin();
    $channel = core()->getDefaultChannel()->code;
    $url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'louzir_home']);
    $content = new HomeContent;
    $payload = ['channel' => $channel, 'locale' => 'fr'];
    foreach (HomeContent::schema() as $group => $fields) {
        foreach ($fields as $name => $field) {
            if ($field['type'] !== 'image') {
                $payload['general']['louzir_home'][$group][$name] = $content->value($name, $channel, 'fr');
            }
        }
    }

    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });
    $start = microtime(true);
    $response = $this->get($url)->assertOk();
    $getTime = (microtime(true) - $start) * 1000;
    $getReads = collect($queries)->filter(fn ($sql) => str_contains($sql, 'from `core_config`'))->count();
    expect($getReads)->toBeLessThan(20);

    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    foreach ($document->getElementsByTagName('input') as $input) {
        if ($input->getAttribute('name') === 'keys[]') {
            $payload['keys'][] = $input->getAttribute('value');
        }
    }
    expect($payload['keys'])->toHaveCount(count(HomeContent::schema()));
    $this->post($url, $payload)->assertSessionHasNoErrors();

    $queries = [];
    $start = microtime(true);
    $this->post($url, $payload)->assertSessionHasNoErrors();
    $postTime = (microtime(true) - $start) * 1000;
    $writes = collect($queries)->filter(fn ($sql) => preg_match('/^(?:update `core_config`|insert into `core_config`)/i', $sql));
    expect($writes)->toBeEmpty();

    if (getenv('CONFIGURATION_BENCHMARK')) {
        echo json_encode(['get_ms' => round($getTime), 'get_config_reads' => $getReads, 'unchanged_save_ms' => round($postTime), 'save_queries' => count($queries), 'metadata_groups' => count($payload['keys'])]).PHP_EOL;
    }
});
