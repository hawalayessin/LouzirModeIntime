<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Models\CoreConfig;
use Webkul\Shop\Helpers\HomeContent;

beforeEach(function () {
    $this->loginAsAdmin();
    $this->channel = core()->getDefaultChannel();
    $this->url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'louzir_home']);
    $this->payload = ['channel' => $this->channel->code, 'locale' => 'fr'];
    CoreConfig::where('code', 'like', 'general.louzir_home.%')->delete();
});

it('exposes scrolling and product controls in the admin editor', function () {
    $this->get($this->url)->assertOk()
        ->assertSee('general[louzir_home][marquee][marquee-speed]', false)
        ->assertSee('general[louzir_home][marquee][marquee-enabled]', false)
        ->assertSee('general[louzir_home][signature][signature-sort]', false)
        ->assertSee('general[louzir_home][boutiques][sahloul-image]', false);
});

it('saves the complete form with the field metadata rendered by the admin page', function () {
    $response = $this->get($this->url)->assertOk()
        ->assertSee('one_of:created_at-desc,name-asc,price-asc,price-desc', false)
        ->assertDontSee('min_value_value', false);
    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $payload = $this->payload;
    foreach ($document->getElementsByTagName('input') as $input) {
        if ($input->getAttribute('name') === 'keys[]') {
            $payload['keys'][] = $input->getAttribute('value');
        }
    }

    $content = new HomeContent;
    foreach (HomeContent::schema() as $group => $fields) {
        foreach ($fields as $name => $field) {
            if ($field['type'] !== 'image') {
                $payload['general']['louzir_home'][$group][$name] = $content->value($name, $this->channel->code, 'fr');
            }
        }
    }
    $payload['general']['louzir_home']['marquee']['marquee-speed'] = '27';

    expect($payload['keys'])->not->toBeEmpty();
    $this->post($this->url, $payload)->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
    $this->assertDatabaseHas('core_config', [
        'code' => 'general.louzir_home.marquee.marquee-speed', 'value' => '27', 'channel_code' => $this->channel->code,
    ]);
});

it('publishes localized ticker text and shared speed with pause controls', function () {
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['marquee' => [
            'marquee-enabled' => 1, 'marquee-speed' => 24,
            'marquee-item-1' => '<b>Collection test</b> {{ 7 * 7 }}',
        ]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $this->get('/?locale=fr')->assertOk()
        ->assertSee('--marquee-duration: 24s', false)
        ->assertSee('&lt;b&gt;Collection test&lt;/b&gt; {{ 7 * 7 }}', false)
        ->assertSee('class="louzir-marquee-toggle"', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertDontSee('<b>Collection test</b>', false);
    $this->get('/?locale=en')->assertOk()->assertDontSee('Collection test');
    $this->assertDatabaseHas('core_config', [
        'code' => 'general.louzir_home.marquee.marquee-speed', 'value' => '24', 'locale_code' => null,
    ]);
});

it('allows sections to be disabled and product selection to be configured', function () {
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => [
            'marquee' => ['marquee-enabled' => 0],
            'hero' => ['hero-enabled' => 0],
            'services' => ['services-enabled' => 0],
            'collections' => ['collections-enabled' => 0],
            'boutiques' => ['boutiques-enabled' => 0],
            'community' => ['community-enabled' => 0],
            'signature' => ['signature-category' => 12, 'signature-limit' => 6, 'signature-sort' => 'price-asc'],
        ]],
    ]))->assertSessionHasNoErrors();

    $this->get('/?locale=fr')->assertOk()
        ->assertDontSee('<section class="louzir-hero"', false)
        ->assertDontSee('class="louzir-marquee"', false)
        ->assertDontSee('id="collections"', false)
        ->assertDontSee('id="boutiques"', false)
        ->assertSee('category_id=12', false)
        ->assertSee('limit=6', false)
        ->assertSee('sort=price-asc', false);
});

it('validates motion and catalog settings on the server', function ($group, $field, $value) {
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => [$group => [$field => $value]]],
    ]))->assertSessionHasErrors('general.louzir_home.'.$group.'.'.$field);
})->with([
    ['marquee', 'marquee-speed', 0],
    ['marquee', 'marquee-speed', 121],
    ['marquee', 'marquee-enabled', 'invalid'],
    ['signature', 'signature-limit', 13],
    ['signature', 'signature-category', -1],
    ['signature', 'signature-sort', 'invalid'],
]);

it('uploads store imagery and restores the local boutique cover after removal', function () {
    Storage::fake(config('filesystems.default'));
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['boutiques' => ['sahloul-image' => UploadedFile::fake()->image('store.png')]]],
    ]))->assertSessionHasNoErrors();
    $record = CoreConfig::where('code', 'general.louzir_home.boutiques.sahloul-image')->firstOrFail();
    Storage::assertExists($record->value);
    $this->get('/')->assertOk()->assertSee(Storage::url($record->value), false);

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['boutiques' => ['sahloul-image' => ['delete' => 1]]]],
    ]))->assertSessionHasNoErrors();

    expect((new HomeContent)->value('sahloul-image'))->toBe(asset('assets/louzirsahloul.png'));
});

it('ships all referenced home images as valid local assets', function () {
    foreach (HomeContent::schema() as $fields) {
        foreach ($fields as $field) {
            if ($field['type'] !== 'image') {
                continue;
            }

            $file = public_path($field['fallback']);
            expect(is_file($file))->toBeTrue();
            $metadata = getimagesize($file);
            expect($metadata[0])->toBeGreaterThan(200)
                ->and($metadata['mime'])->toBeIn(['image/webp', 'image/png', 'image/jpeg']);
            if (str_starts_with($field['fallback'], 'assets/home/stitch-2026/')) {
                expect($metadata[0])->toBeGreaterThan(1000);
            }
        }
    }
});

it('opens the configured public Maps link rather than an embed URL', function () {
    $embed = 'https://www.google.com/maps/embed/v1/place?key=test-key&q=Sahloul';
    $directions = 'https://www.google.com/maps/search/?api=1&query=Louzir+Sahloul';
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['louzir_home' => ['boutiques' => [
            'sahloul-map-url' => $embed,
            'sahloul-directions-url' => $directions,
        ]]],
    ]))->assertSessionHasNoErrors();

    $this->get('/?locale=fr')->assertOk()
        ->assertSee('class="louzir-map-preview" href="'.e($directions).'"', false)
        ->assertDontSee('href="'.e($embed).'"', false)
        ->assertDontSee('<iframe', false)
        ->assertSee('assets/louzirsahloul.png', false)
        ->assertSee('assets/louzirjemmel.png', false)
        ->assertSee('object-fit: contain', false);
});
