<?php

use Webkul\Core\Models\CoreConfig;
use Webkul\Shop\Helpers\HomeContent;

beforeEach(function () {
    $this->loginAsAdmin();
    $this->channel = core()->getDefaultChannel()->code;
    $this->url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'louzir_home']);
    CoreConfig::where('code', 'like', 'general.louzir_home.%')->delete();
    $this->saveMarquee = fn (array $settings) => $this->post($this->url, [
        'channel' => $this->channel, 'locale' => 'fr',
        'general' => ['louzir_home' => ['marquee' => $settings]],
    ]);
});

it('renders native color fields and a form registered home switch', function () {
    $this->get($this->url)->assertOk()
        ->assertSee('general[louzir_home][marquee][marquee-background]', false)
        ->assertSee('general[louzir_home][marquee][marquee-color]', false)
        ->assertSee('general[louzir_home][marquee][marquee-accent]', false)
        ->assertSee('rules="one_of:0,1"', false);
    $sections = collect(require base_path('packages/Louzir/Admin/src/Config/louzir-home.php'));
    $fields = collect($sections->firstWhere('key', 'general.louzir_home.marquee')['fields']);
    foreach (['marquee-background', 'marquee-color', 'marquee-accent'] as $name) {
        expect($fields->firstWhere('name', $name)['type'])->toBe('color')
            ->and($fields->firstWhere('name', $name)['locale_based'])->toBeFalse();
    }
});

it('publishes colors across locales without touching another channel', function () {
    ($this->saveMarquee)([
        'marquee-enabled' => '1', 'marquee-background' => '#243b53',
        'marquee-color' => '#ffffff', 'marquee-accent' => '#FFCC88',
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    foreach (['fr', 'en'] as $locale) {
        $this->get('/?locale='.$locale)->assertOk()
            ->assertSee('--marquee-background: #243b53', false)
            ->assertSee('--marquee-color: #ffffff', false)
            ->assertSee('--marquee-accent: #FFCC88', false);
    }
    expect((new HomeContent)->value('marquee-background', 'second', 'fr'))->toBe('#151515');
    $this->assertDatabaseHas('core_config', [
        'code' => 'general.louzir_home.marquee.marquee-background',
        'channel_code' => $this->channel, 'locale_code' => null, 'value' => '#243b53',
    ]);
});

it('disables and re enables the marquee without losing its colors', function () {
    ($this->saveMarquee)(['marquee-enabled' => '0', 'marquee-background' => '#243b53'])->assertSessionHasNoErrors();
    $this->get('/')->assertOk()->assertDontSee('class="louzir-marquee"', false);
    ($this->saveMarquee)(['marquee-enabled' => '1'])->assertSessionHasNoErrors();
    $this->get('/')->assertOk()->assertSee('class="louzir-marquee"', false)->assertSee('--marquee-background: #243b53', false);
});

it('rejects unsafe color values independently of client metadata', function ($value) {
    ($this->saveMarquee)(['marquee-background' => $value])->assertSessionHasErrors('general.louzir_home.marquee.marquee-background');
})->with(['red', '#fff', '#gggggg', '#112233; color:red', 'url(https://example.com)', [['#ffffff']]]);

it('uses the original palette for empty or corrupt saved colors', function () {
    CoreConfig::create([
        'code' => 'general.louzir_home.marquee.marquee-background',
        'channel_code' => $this->channel, 'locale_code' => null,
        'value' => '#112233; background:url(https://example.com)',
    ]);
    expect((new HomeContent)->value('marquee-background'))->toBe('#151515');
    ($this->saveMarquee)(['marquee-background' => ''])->assertSessionHasNoErrors();
    expect((new HomeContent)->value('marquee-background'))->toBe('#151515');
});

it('preserves the submitted switch state when another setting fails validation', function () {
    $this->from($this->url);
    ($this->saveMarquee)(['marquee-enabled' => '0', 'marquee-speed' => 0])
        ->assertSessionHasErrors('general.louzir_home.marquee.marquee-speed')
        ->assertSessionHasInput('general.louzir_home.marquee.marquee-enabled', '0');
    $response = $this->get($this->url)->assertOk();
    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    foreach ($document->getElementsByTagName('v-configurable') as $field) {
        if ($field->getAttribute('name') === 'general[louzir_home][marquee][marquee-enabled]') {
            expect($field->getAttribute('value'))->toBe('0');

            return;
        }
    }
    $this->fail('The marquee visibility field was not rendered.');
});

it('does not store validation metadata as configuration values', function () {
    CoreConfig::where('code', 'like', 'keys.%')->delete();
    $this->post($this->url, [
        'channel' => $this->channel, 'locale' => 'fr',
        'keys' => [json_encode([
            'key' => 'general.louzir_home.marquee',
            'fields' => [['name' => 'marquee-enabled', 'validation' => 'boolean']],
        ])],
        'general' => ['louzir_home' => ['marquee' => ['marquee-enabled' => '0']]],
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    $this->assertDatabaseMissing('core_config', ['code' => 'keys.0']);
    $this->assertDatabaseHas('core_config', [
        'code' => 'general.louzir_home.marquee.marquee-enabled', 'value' => '0',
    ]);
});
