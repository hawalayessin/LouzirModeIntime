<?php

use Illuminate\Support\Facades\Cache;
use Webkul\Shop\Services\RecommendationSettings;

beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();
    $this->loginAsAdmin();
    $this->url = route('admin.configuration.store', ['slug' => 'catalog', 'slug2' => 'products']);
    $this->payload = ['channel' => core()->getDefaultChannel()->code, 'locale' => 'fr'];
});

it('exposes related product strategy limits stock and cache settings in admin', function () {
    $this->get($this->url)->assertOk()
        ->assertSee('catalog[products][product_view_page][related_mode]', false)
        ->assertSee('catalog[products][product_view_page][related_only_saleable]', false)
        ->assertSee('catalog[products][product_view_page][related_cache_seconds]', false);
    $this->post($this->url, $this->payload + ['catalog' => ['products' => ['product_view_page' => [
        'no_of_related_products' => '0', 'no_of_up_sells_products' => '0', 'related_mode' => 'manual',
        'related_only_saleable' => '0', 'related_cache_seconds' => '120',
    ]]]])->assertSessionHasNoErrors()->assertSessionHas('success');
    $settings = RecommendationSettings::current();
    expect($settings->limit)->toBe(0)->and($settings->upSellLimit)->toBe(0)->and($settings->mode)->toBe('manual')
        ->and($settings->onlySaleable)->toBeFalse()->and($settings->cacheSeconds)->toBe(120);
});

it('validates recommendation settings without trusting submitted metadata', function ($field, $value) {
    $this->post($this->url, $this->payload + ['catalog' => ['products' => ['product_view_page' => [$field => $value]]]])
        ->assertSessionHasErrors('catalog.products.product_view_page.'.$field);
})->with([
    ['no_of_related_products', '-1'], ['no_of_related_products', '13'], ['no_of_up_sells_products', '10000'],
    ['related_mode', 'random'], ['related_only_saleable', 'yes'], ['related_cache_seconds', '29'], ['related_cache_seconds', '1801'],
]);
