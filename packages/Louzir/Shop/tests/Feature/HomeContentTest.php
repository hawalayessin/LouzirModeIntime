<?php

use Illuminate\Support\Facades\DB;
use Webkul\Shop\Helpers\HomeContent;

it('isolates home text by locale and channel in the same helper instance', function () {
    DB::table('core_config')->where('code', 'like', 'general.louzir_home.%')->delete();

    foreach ([
        ['default', null, 'Legacy shared text'],
        ['default', 'en', 'English collection'],
        ['default', 'fr', 'Collection francaise'],
        ['second', 'fr', 'Second channel'],
    ] as [$channel, $locale, $value]) {
        DB::table('core_config')->insert([
            'code' => 'general.louzir_home.hero.hero-discover',
            'channel_code' => $channel,
            'locale_code' => $locale,
            'value' => $value,
        ]);
    }

    $content = new HomeContent;

    expect($content->value('hero-discover', 'default', 'fr'))->toBe('Collection francaise')
        ->and($content->value('hero-discover', 'default', 'en'))->toBe('English collection')
        ->and($content->value('hero-discover', 'second', 'fr'))->toBe('Second channel')
        ->and($content->value('hero-discover', 'default', 'ar'))
        ->toBe(trans('shop::app.home.storefront.hero-discover', [], 'ar'));
});

it('keeps links shared and uses translations for empty localized text', function () {
    DB::table('core_config')->where('code', 'like', 'general.louzir_home.%')->delete();
    DB::table('core_config')->insert([
        ['code' => 'general.louzir_home.hero.hero-discover-url', 'channel_code' => 'default', 'locale_code' => null, 'value' => '/collection'],
        ['code' => 'general.louzir_home.hero.hero-discover', 'channel_code' => 'default', 'locale_code' => 'fr', 'value' => ''],
        ['code' => 'general.louzir_home.hero.image-1', 'channel_code' => 'default', 'locale_code' => 'en', 'value' => 'configuration/home.jpg'],
    ]);

    $content = new HomeContent;

    expect($content->value('hero-discover-url', 'default', 'fr'))->toBe('/collection')
        ->and($content->value('hero-discover-url', 'default', 'ar'))->toBe('/collection')
        ->and($content->value('hero-discover', 'default', 'fr'))
        ->toBe(trans('shop::app.home.storefront.hero-discover', [], 'fr'))
        ->and($content->value('image-1', 'default', 'ar'))
        ->toBe($content->value('image-1', 'default', 'en'));
});

it('preserves legacy content without replacing existing translations', function () {
    DB::table('core_config')->where('code', 'like', 'general.louzir_home.%')->delete();
    DB::table('core_config')->insert([
        ['code' => 'general.louzir_home.hero.hero-title-start', 'channel_code' => 'default', 'locale_code' => null, 'value' => 'Custom title'],
        ['code' => 'general.louzir_home.hero.hero-discover', 'channel_code' => 'default', 'locale_code' => null, 'value' => 'Old text'],
        ['code' => 'general.louzir_home.hero.hero-discover', 'channel_code' => 'default', 'locale_code' => 'en', 'value' => 'Latest English text'],
        ['code' => 'general.louzir_home.hero.hero-discover-url', 'channel_code' => 'default', 'locale_code' => null, 'value' => '/collection'],
    ]);

    $migration = require base_path('database/migrations/2026_10_13_000001_localize_louzir_home_content.php');
    $migration->up();
    $migration->up();
    $content = new HomeContent;

    expect($content->value('hero-title-start', 'default', 'en'))->toBe('Custom title')
        ->and($content->value('hero-discover', 'default', 'en'))->toBe('Latest English text')
        ->and($content->value('hero-title-start', 'default', 'fr'))
        ->toBe(trans('shop::app.home.storefront.hero-title-start', [], 'fr'))
        ->and(DB::table('core_config')->where('code', 'like', 'general.louzir_home.%')->count())->toBe(5);
});

it('configures admin text fields as localized and media as shared', function () {
    $sections = require base_path('packages/Louzir/Admin/src/Config/louzir-home.php');
    $fields = collect($sections)->flatMap(fn ($section) => $section['fields'] ?? [])->keyBy('name');

    expect($fields['hero-discover']['locale_based'])->toBeTrue()
        ->and($fields['image-1']['locale_based'])->toBeFalse()
        ->and($fields['hero-discover-url']['locale_based'])->toBeFalse();
});

it('uses the direct Facebook page and updates saved legacy search links', function () {
    $code = 'general.louzir_home.hero.facebook-url';
    $direct = 'https://www.facebook.com/profile.php?id=61569721747533&locale=fr_FR';
    DB::table('core_config')->where('code', $code)->delete();
    expect((new HomeContent)->value('facebook-url', 'default', 'fr'))->toBe($direct);

    DB::table('core_config')->insert([
        ['code' => $code, 'channel_code' => 'default', 'locale_code' => null, 'value' => 'https://www.facebook.com/search/top/?q=louzir%20mode%20intime&locale=fr_FR'],
        ['code' => $code, 'channel_code' => 'second', 'locale_code' => null, 'value' => 'https://www.facebook.com/custom-page'],
    ]);
    $migration = require base_path('database/migrations/2026_10_21_000001_update_louzir_facebook_page_url.php');
    $migration->up();
    $migration->up();

    expect((new HomeContent)->value('facebook-url', 'default', 'fr'))->toBe($direct)
        ->and((new HomeContent)->value('facebook-url', 'second', 'fr'))->toBe('https://www.facebook.com/custom-page');
    $this->get('/?locale=fr')->assertOk()
        ->assertSee('href="'.e($direct).'"', false)
        ->assertDontSee('facebook.com/search/top/', false);
});
