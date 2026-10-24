<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Core\Models\CoreConfig;
use Webkul\Shop\Helpers\HomeContent;

beforeEach(function () {
    $this->channel = core()->getDefaultChannel();
    $this->locale = $this->channel->default_locale->code;
    $this->url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'louzir_home']);
    $this->payload = [
        'channel' => $this->channel->code,
        'locale' => $this->locale,
    ];

    CoreConfig::where('code', 'like', 'general.louzir_home.%')->delete();
});

it('exposes the existing home content in the admin editor', function () {
    $this->loginAsAdmin();

    $this->get($this->url)
        ->assertOk()
        ->assertSee('general[louzir_home][hero][hero-title-start]', false)
        ->assertSee('general[louzir_home][hero][image-1]', false);
});

it('saves admin text and links and renders them without changing the home layout', function () {
    $this->loginAsAdmin();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => [
            'hero' => ['hero-title-start' => 'Louzir test collection', 'hero-discover-url' => '/test-collection'],
            'collections' => ['feminine-url' => '/test-lingerie'],
        ]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $this->assertDatabaseHas('core_config', [
        'code' => 'general.louzir_home.hero.hero-title-start',
        'value' => 'Louzir test collection',
        'channel_code' => $this->channel->code,
        'locale_code' => $this->locale,
    ]);

    $this->get(route('shop.home.index', ['locale' => $this->locale]))
        ->assertOk()
        ->assertSee('Louzir test collection')
        ->assertSee('href="/test-collection"', false)
        ->assertSee('href="/test-lingerie"', false)
        ->assertSee('class="louzir-shell louzir-hero-grid"', false)
        ->assertSee('class="louzir-bento"', false);
});

it('escapes edited content and prevents Vue template evaluation', function () {
    $this->loginAsAdmin();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['hero' => [
            'hero-title-start' => '<script>alert(1)</script> {{ 7 * 7 }}',
        ]]],
    ]))->assertSessionHasNoErrors();

    $this->get(route('shop.home.index', ['locale' => $this->locale]))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt; {{ 7 * 7 }}', false)
        ->assertSee('class="louzir-hero louzir-carousel"', false)
        ->assertSee('data-interval="5" v-pre', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('rejects unsafe links even without client validation metadata', function ($field, $value) {
    $this->loginAsAdmin();
    $group = str_contains($field, 'map') ? 'boutiques' : 'hero';

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => [$group => [$field => $value]]],
    ]))->assertSessionHasErrors('general.louzir_home.'.$group.'.'.$field);
})->with([
    ['hero-discover-url', 'javascript:alert(1)'],
    ['hero-discover-url', '//example.org'],
    ['hero-discover-url', "https://exam\nple.org"],
    ['sahloul-map-url', 'https://example.org/maps'],
]);

it('uploads a home image and restores the original image when removed', function () {
    $this->loginAsAdmin();
    Storage::fake(config('filesystems.default'));

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['hero' => [
            'image-1' => UploadedFile::fake()->image('banner.png', 600, 800),
        ]]],
    ]))->assertSessionHasNoErrors();

    $image = CoreConfig::where('code', 'general.louzir_home.hero.image-1')
        ->where('channel_code', $this->channel->code)
        ->whereNull('locale_code')->firstOrFail()->value;

    Storage::assertExists($image);
    expect(app(HomeContent::class)->value('image-1', $this->channel->code, $this->locale))
        ->toBe(Storage::url($image));

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['hero' => ['image-1' => ['delete' => 1]]]],
    ]))->assertSessionHasNoErrors();

    expect(app(HomeContent::class)->value('image-1', $this->channel->code, $this->locale))
        ->toBe(asset('assets/home/hero-carousel/lingerie.webp'));
});

it('isolates localized edits across languages and channels', function () {
    CoreConfig::updateOrCreate([
        'code' => 'general.louzir_home.hero.hero-title-start',
        'channel_code' => $this->channel->code,
        'locale_code' => 'fr',
    ], ['value' => 'Collection francaise']);

    $content = app(HomeContent::class);

    expect($content->value('hero-title-start', $this->channel->code, 'fr'))
        ->toBe('Collection francaise');
    expect($content->value('hero-title-start', 'other-test-channel', 'fr'))
        ->toBe(trans('shop::app.home.storefront.hero-title-start', [], 'fr'));
    expect($content->value('hero-title-start', $this->channel->code, 'ar'))
        ->toBe(trans('shop::app.home.storefront.hero-title-start', [], 'ar'));
    expect($content->value('hero-title-start', $this->channel->code, 'en'))
        ->toBe(trans('shop::app.home.storefront.hero-title-start', [], 'en'));
});

it('publishes an English admin edit only in the English storefront', function () {
    $this->loginAsAdmin();

    $this->post($this->url, array_merge($this->payload, [
        'locale' => 'en',
        'general' => ['louzir_home' => ['hero' => ['hero-title-start' => 'Shared Louzir title']]],
    ]))->assertSessionHasNoErrors();

    $this->get(route('shop.home.index', ['locale' => 'en']))->assertOk()->assertSee('Shared Louzir title');

    foreach (['fr', 'ar'] as $locale) {
        $this->get(route('shop.home.index', ['locale' => $locale]))
            ->assertOk()->assertDontSee('Shared Louzir title');
    }
});

it('invalidates the storefront response cache after an admin save', function () {
    $this->loginAsAdmin();
    ResponseCache::shouldReceive('clear')->once();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['community' => ['community-title' => 'Updated community']]],
    ]))->assertSessionHasNoErrors();
});

it('does not allow an unauthenticated visitor to edit the home page', function () {
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['hero' => ['hero-title-start' => 'Unauthorized change']]],
    ]))->assertRedirect();
    $this->assertDatabaseMissing('core_config', ['value' => 'Unauthorized change']);
});
