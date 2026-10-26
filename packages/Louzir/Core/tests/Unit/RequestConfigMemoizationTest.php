<?php

use Illuminate\Http\Request;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Core\SystemConfig;

it('keeps read-only config snapshots inside the request rather than the long lived service', function () {
    $original = request();
    $repository = Mockery::mock(CoreConfigRepository::class);
    $repository->shouldReceive('findOneWhere')->twice()->andReturn(new CoreConfig(['value' => 'hybrid']), new CoreConfig(['value' => 'manual']));
    $config = new SystemConfig($repository);
    try {
        app()->instance('request', Request::create('/first'));
        $config->memoizeForRequest();
        expect($config->getConfigData('catalog.products.product_view_page.related_mode', 'default', 'fr'))->toBe('hybrid')
            ->and($config->getConfigData('catalog.products.product_view_page.related_mode', 'default', 'fr'))->toBe('hybrid');
        app()->instance('request', Request::create('/next'));
        $config->memoizeForRequest();
        expect($config->getConfigData('catalog.products.product_view_page.related_mode', 'default', 'fr'))->toBe('manual');
    } finally {
        app()->instance('request', $original);
    }
});

it('keeps the existing non-memoized config behavior for other endpoints', function () {
    $repository = Mockery::mock(CoreConfigRepository::class);
    $repository->shouldReceive('findOneWhere')->twice()->andReturn(new CoreConfig(['value' => 'hybrid']), new CoreConfig(['value' => 'manual']));
    $config = new SystemConfig($repository);
    expect($config->getConfigData('catalog.products.product_view_page.related_mode', 'default', 'fr'))->toBe('hybrid')
        ->and($config->getConfigData('catalog.products.product_view_page.related_mode', 'default', 'fr'))->toBe('manual');
});

it('separates locale and channel values in the same read-only config request', function () {
    $original = request();
    $repository = Mockery::mock(CoreConfigRepository::class);
    $repository->shouldReceive('findOneWhere')->with(['code' => 'general.louzir_home.hero.hero-kicker', 'channel_code' => 'default', 'locale_code' => 'fr'])->once()->andReturn(new CoreConfig(['value' => 'Français']));
    $repository->shouldReceive('findOneWhere')->with(['code' => 'general.louzir_home.hero.hero-kicker', 'channel_code' => 'default', 'locale_code' => 'en'])->once()->andReturn(new CoreConfig(['value' => 'English']));
    $repository->shouldReceive('findOneWhere')->with(['code' => 'general.louzir_home.hero.hero-kicker', 'channel_code' => 'other', 'locale_code' => 'fr'])->once()->andReturn(new CoreConfig(['value' => 'Autre canal']));
    $config = new SystemConfig($repository);
    try {
        app()->instance('request', Request::create('/scope'));
        $config->memoizeForRequest();
        expect($config->getConfigData('general.louzir_home.hero.hero-kicker', 'default', 'fr'))->toBe('Français')
            ->and($config->getConfigData('general.louzir_home.hero.hero-kicker', 'default', 'en'))->toBe('English')
            ->and($config->getConfigData('general.louzir_home.hero.hero-kicker', 'other', 'fr'))->toBe('Autre canal')
            ->and($config->getConfigData('general.louzir_home.hero.hero-kicker', 'default', 'fr'))->toBe('Français');
    } finally {
        app()->instance('request', $original);
    }
});
