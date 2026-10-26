<?php

use Webkul\Core\Models\CoreConfig;
use Webkul\Theme\Facades\Themes as ThemeFacade;
use Webkul\Theme\Themes;

beforeEach(function () {
    $this->loginAsAdmin();
    $this->channel = core()->getDefaultChannel()->code;
    $this->url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'louzir_home']);
    CoreConfig::where('code', 'like', 'general.louzir_home.motion.%')->delete();
    $this->saveMotion = fn (array $settings) => $this->post($this->url, [
        'channel' => $this->channel, 'locale' => 'fr',
        'general' => ['louzir_home' => ['motion' => $settings]],
    ]);
});

it('exposes animation controls and publishes their saved values across languages', function () {
    $this->get($this->url)->assertOk()
        ->assertSee('general[louzir_home][motion][motion-enabled]', false)
        ->assertSee('general[louzir_home][motion][motion-duration]', false)
        ->assertSee('general[louzir_home][motion][motion-stagger]', false);
    ($this->saveMotion)(['motion-enabled' => '1', 'motion-duration' => '500', 'motion-stagger' => '80'])
        ->assertSessionHasNoErrors()->assertSessionHas('success');
    foreach (['fr', 'en', 'ar'] as $locale) {
        // Each real HTTP request resolves its own admin or storefront theme.
        ThemeFacade::clearResolvedInstance(Themes::class);
        $this->get('/?locale='.$locale)->assertOk()
            ->assertSee('data-motion-enabled="1"', false)
            ->assertSee('data-motion-duration="500"', false)
            ->assertSee('data-motion-stagger="80"', false)
            ->assertSee('--louzir-motion-duration: 500ms', false);
        $this->get('/search?query=test')->assertOk()->assertSee('transform: perspective(1000px)', false);
    }
});

it('disables entrance effects without changing carousel autoplay or merchant content', function () {
    CoreConfig::create(['code' => 'general.louzir_home.hero.hero-autoplay', 'value' => '1', 'channel_code' => $this->channel]);
    ($this->saveMotion)(['motion-enabled' => '0'])->assertSessionHasNoErrors()->assertSessionHas('success');
    $this->get('/?locale=fr')->assertOk()
        ->assertSee('data-motion-enabled="0"', false)
        ->assertSee('data-autoplay="1"', false)
        ->assertDontSee('transform: perspective(1000px)', false);
    expect(CoreConfig::where('code', 'general.louzir_home.motion.motion-enabled')->value('value'))->toBe('0');
});

it('validates animation settings on the server without client metadata', function ($field, $value) {
    ($this->saveMotion)([$field => $value])->assertSessionHasErrors('general.louzir_home.motion.'.$field);
})->with([
    ['motion-enabled', 'yes'], ['motion-duration', '249'], ['motion-duration', '1001'],
    ['motion-duration', 'NaN'], ['motion-stagger', '-1'], ['motion-stagger', '121'],
]);
