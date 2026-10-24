<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Models\CoreConfig;
use Webkul\Shop\Helpers\HomeContent;

beforeEach(function () {
    $this->loginAsAdmin();
    $this->channel = core()->getDefaultChannel()->code;
    $this->url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'louzir_home']);
    CoreConfig::where('code', 'like', 'general.louzir_home.%')->delete();
    $this->saveHero = fn (array $settings, string $locale = 'fr') => $this->post($this->url, [
        'channel' => $this->channel, 'locale' => $locale, 'general' => ['louzir_home' => $settings],
    ]);
});

it('renders all slide controls and default image previews in the admin', function () {
    $this->get($this->url)->assertOk()
        ->assertSee('general[louzir_home][hero][hero-interval]', false)
        ->assertSee('general[louzir_home][hero][image-1]', false)
        ->assertSee('general[louzir_home][hero_slide_2][hero-2-image]', false)
        ->assertSee('general[louzir_home][hero_slide_3][hero-3-mobile-x]', false)
        ->assertSee('assets/home/hero-carousel/homewear.webp', false);
});

it('uploads replaces and removes each slide image through admin and publishes it across locales', function (string $group, string $field) {
    Storage::fake(config('filesystems.default'));
    ($this->saveHero)([$group => [$field => UploadedFile::fake()->image('hero.png', 1920, 1080)]])->assertSessionHasNoErrors();
    $code = 'general.louzir_home.'.$group.'.'.$field;
    $record = CoreConfig::where('code', $code)->firstOrFail();
    $first = $record->value;
    Storage::assertExists($first);
    $this->get('/?locale=fr')->assertOk()->assertSee(Storage::url($first), false);
    $this->get('/?locale=en')->assertOk()->assertSee(Storage::url($first), false);
    expect($record->locale_code)->toBeNull();

    ($this->saveHero)([$group => [$field => UploadedFile::fake()->image('replacement.jpg', 1600, 900)]])->assertSessionHasNoErrors();
    $replacement = CoreConfig::where('code', $code)->firstOrFail()->value;
    expect($replacement)->not->toBe($first);
    Storage::assertMissing($first);
    Storage::assertExists($replacement);
    $this->get('/')->assertOk()->assertSee(Storage::url($replacement), false)->assertDontSee(Storage::url($first), false);

    ($this->saveHero)([$group => [$field => ['delete' => 1]]])->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('core_config', ['code' => $code]);
    expect((new HomeContent)->value($field))->toContain('/assets/home/hero-carousel/');
})->with([['hero', 'image-1'], ['hero_slide_2', 'hero-2-image'], ['hero_slide_3', 'hero-3-image']]);

it('publishes translated copy and shared timing framing and links', function () {
    ($this->saveHero)([
        'hero' => ['hero-autoplay' => 0, 'hero-interval' => 9],
        'hero_slide_2' => ['hero-2-title' => '<b>Soie</b> {{ 7 * 7 }}', 'hero-2-url' => '/search?q=soie', 'hero-2-position-x' => 80, 'hero-2-mobile-x' => 90, 'hero-2-overlay' => 70],
    ])->assertSessionHasNoErrors();
    $this->get('/?locale=fr')->assertOk()
        ->assertSee('&lt;b&gt;Soie&lt;/b&gt; {{ 7 * 7 }}', false)
        ->assertSee('data-autoplay="0"', false)->assertSee('data-interval="9"', false)
        ->assertSee('--hero-x: 80%', false)->assertSee('--hero-mobile-x: 90%', false)
        ->assertSee('--hero-overlay: 0.7', false)->assertSee('href="/search?q=soie"', false)
        ->assertDontSee('<b>Soie</b>', false);
    $this->get('/?locale=en')->assertOk()->assertDontSee('&lt;b&gt;Soie', false)->assertSee('href="/search?q=soie"', false);
});

it('handles one or no enabled slides without empty navigation', function () {
    ($this->saveHero)(['hero_slide_2' => ['hero-2-enabled' => 0], 'hero_slide_3' => ['hero-3-enabled' => 0]])->assertSessionHasNoErrors();
    $html = $this->get('/')->assertOk()->getContent();
    expect(substr_count($html, 'aria-roledescription="slide"'))->toBe(1)
        ->and($html)->not->toContain('class="louzir-carousel-controls"');
    ($this->saveHero)(['hero' => ['hero-slide-1-enabled' => 0]])->assertSessionHasNoErrors();
    $this->get('/')->assertOk()->assertDontSee('aria-roledescription="carousel"', false);
});

it('promotes the first enabled slide to the main heading and priority image', function () {
    ($this->saveHero)(['hero' => ['hero-slide-1-enabled' => 0]])->assertSessionHasNoErrors();
    $this->get('/?locale=fr')->assertOk()->assertSee('<h1 class="louzir-title">L’art de ralentir</h1>', false)
        ->assertSee('fetchpriority="high"', false);
});

it('rejects invalid carousel settings even without client metadata', function (string $group, string $field, mixed $value) {
    ($this->saveHero)([$group => [$field => $value]])->assertSessionHasErrors('general.louzir_home.'.$group.'.'.$field);
})->with([
    ['hero', 'hero-interval', 2], ['hero', 'hero-interval', 31], ['hero', 'hero-autoplay', 'bad'],
    ['hero_slide_2', 'hero-2-position-x', 101], ['hero_slide_3', 'hero-3-mobile-x', -1],
    ['hero_slide_2', 'hero-2-overlay', 91], ['hero_slide_2', 'hero-2-url', 'javascript:alert(1)'],
]);

it('rejects non image uploads', function () {
    ($this->saveHero)(['hero_slide_2' => ['hero-2-image' => UploadedFile::fake()->create('hero.txt', 10, 'text/plain')]])
        ->assertSessionHasErrors('general.louzir_home.hero_slide_2.hero-2-image');
});
