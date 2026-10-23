<?php

use Illuminate\Support\Facades\DB;
use Webkul\Shop\Helpers\HeaderOffer;

it('defaults to a black announcement bar with white text', function () {
    DB::table('core_config')->where('code', 'general.content.header_offer.background_color')->delete();
    expect(app(HeaderOffer::class)->colors())->toBe(['background' => '#1a1a1a', 'text' => '#ffffff']);
});

it('uses the configured background and chooses legible text', function (string $color, string $text) {
    DB::table('core_config')->where('code', 'general.content.header_offer.background_color')->delete();
    DB::table('core_config')->insert(['code' => 'general.content.header_offer.background_color', 'value' => $color]);
    expect(app(HeaderOffer::class)->colors())->toBe(['background' => $color, 'text' => $text]);
    $styles = view('shop::components.layouts.header.topbar-styles')->render();
    expect($styles)->toContain('background: '.$color)->toContain('color: '.$text);
})->with([
    ['#1a1a1a', '#ffffff'], ['#223344', '#ffffff'], ['#ffffff', '#000000'],
    ['#ffeecc', '#000000'], ['#777777', '#000000'],
]);

it('falls back safely for invalid legacy color values', function () {
    DB::table('core_config')->where('code', 'general.content.header_offer.background_color')->delete();
    DB::table('core_config')->insert(['code' => 'general.content.header_offer.background_color', 'value' => 'red; background: url(https://example.com)']);
    expect(app(HeaderOffer::class)->colors())->toBe(['background' => '#1a1a1a', 'text' => '#ffffff']);
});

it('includes the announcement once on pages with and without the standard header', function (string $route) {
    $response = $this->get(route($route))->assertOk();
    expect(substr_count($response->getContent(), 'class="louzir-topbar max-lg:hidden"'))->toBe(1);
})->with(['shop.home.index', 'shop.customer.session.index', 'shop.customers.register.index', 'shop.checkout.cart.index']);
