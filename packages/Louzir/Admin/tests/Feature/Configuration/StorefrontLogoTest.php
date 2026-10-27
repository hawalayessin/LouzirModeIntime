<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Core\Models\CoreConfig;

beforeEach(function () {
    $this->channel = core()->getDefaultChannel();
    $this->url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'design']);
    $this->payload = [
        'channel' => $this->channel->code,
        'locale' => $this->channel->default_locale->code,
    ];

    Storage::fake(config('filesystems.default'));
    CoreConfig::where('code', 'general.design.admin_logo.logo_image')->delete();
});

it('publishes the design logo on the storefront and customer authentication pages', function () {
    $this->loginAsAdmin();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => [
            'logo_image' => UploadedFile::fake()->image('site-logo.png', 300, 120),
        ]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $record = CoreConfig::where('code', 'general.design.admin_logo.logo_image')->firstOrFail();
    Storage::assertExists($record->value);
    expect($record->channel_code)->toBeNull()->and($record->locale_code)->toBeNull();

    foreach (['en', 'fr', 'ar'] as $locale) {
        foreach (['/', '/customer/login', '/customer/register', '/customer/forgot-password'] as $path) {
            $this->get($path.'?locale='.$locale)->assertOk()
                ->assertSee('src="'.Storage::url($record->value).'"', false);
        }
    }
});

it('replaces an uploaded logo and removes the previous file', function () {
    $this->loginAsAdmin();
    Storage::put('configuration/old-logo.png', 'old image');
    CoreConfig::create(['code' => 'general.design.admin_logo.logo_image', 'value' => 'configuration/old-logo.png']);

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => [
            'logo_image' => UploadedFile::fake()->image('new-logo.png', 200, 80),
        ]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $path = CoreConfig::where('code', 'general.design.admin_logo.logo_image')->firstOrFail()->value;
    Storage::assertMissing('configuration/old-logo.png');
    Storage::assertExists($path);
    expect($path)->not->toBe('configuration/old-logo.png');
    $this->get('/')->assertOk()->assertSee('src="'.Storage::url($path).'"', false);
});

it('uses the configured logo in the admin header drawer and both theme settings', function () {
    $this->loginAsAdmin();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => [
            'logo_image' => UploadedFile::fake()->image('wide-brand.png', 1800, 300),
        ]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $path = CoreConfig::where('code', 'general.design.admin_logo.logo_image')->firstOrFail()->value;
    $logoUrl = Storage::url($path);
    $this->get(route('admin.dashboard.index'))->assertOk()
        ->assertSee('id="logo-image"', false)
        ->assertSee('id="logo-image-drawer"', false)
        ->assertSee('src="'.$logoUrl.'"', false)
        ->assertSee('logo: '.json_encode($logoUrl), false)
        ->assertSee('dark_logo: '.json_encode($logoUrl), false);
});

it('restores the channel logo after the design logo is removed', function () {
    $this->loginAsAdmin();
    Storage::put('channels/channel-logo.png', 'channel image');
    Storage::put('configuration/site-logo.png', 'site image');
    $this->channel->forceFill(['logo' => 'channels/channel-logo.png'])->save();
    core()->setCurrentChannel($this->channel->fresh());
    CoreConfig::create(['code' => 'general.design.admin_logo.logo_image', 'value' => 'configuration/site-logo.png']);

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => ['logo_image' => ['delete' => 1]]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $this->assertDatabaseMissing('core_config', ['code' => 'general.design.admin_logo.logo_image']);
    $this->get('/')->assertOk()
        ->assertSee('src="'.Storage::url('channels/channel-logo.png').'"', false);
});

it('clears the storefront response cache when the design logo is saved', function () {
    $this->loginAsAdmin();
    ResponseCache::shouldReceive('clear')->once();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => [
            'logo_image' => UploadedFile::fake()->image('site-logo.png'),
        ]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();
});

it('prevents visitors from changing the site logo', function () {
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => [
            'logo_image' => UploadedFile::fake()->image('visitor-logo.png'),
        ]]],
    ]))->assertRedirect();

    $this->assertDatabaseMissing('core_config', ['code' => 'general.design.admin_logo.logo_image']);
});


it('uses the merchant logo instead of written branding in the footer', function () {
    $this->loginAsAdmin();
    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => [
            'logo_image' => UploadedFile::fake()->image('footer-brand.png', 320, 128),
        ]]],
    ]))->assertSessionHasNoErrors();

    $record = CoreConfig::where('code', 'general.design.admin_logo.logo_image')->firstOrFail();
    $response = $this->get('/?locale=fr')->assertOk();
    $html = $response->getContent();
    $footer = substr($html, strpos($html, '<footer'));
    expect($footer)->toContain('class="louzir-footer-logo"', 'src="'.Storage::url($record->value).'"')
        ->not->toContain('<h2>LOUZIR</h2>', '<p>Mode Intime</p>');
});
