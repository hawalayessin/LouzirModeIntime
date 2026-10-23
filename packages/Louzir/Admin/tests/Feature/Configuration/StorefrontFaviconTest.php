<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Core\Models\CoreConfig;

beforeEach(function () {
    $this->channel = core()->getDefaultChannel();
    $this->url = route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'design']);
    $this->payload = ['channel' => $this->channel->code, 'locale' => $this->channel->default_locale->code];
    Storage::fake(config('filesystems.default'));
    CoreConfig::where('code', 'general.design.admin_logo.favicon')->delete();
});

it('publishes an uploaded design favicon on all storefront layouts and locales', function () {
    $this->loginAsAdmin();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => ['favicon' => UploadedFile::fake()->image('favicon.png', 32, 32)]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $record = CoreConfig::where('code', 'general.design.admin_logo.favicon')->firstOrFail();
    Storage::assertExists($record->value);
    expect($record->channel_code)->toBeNull()->and($record->locale_code)->toBeNull();

    foreach (['en', 'fr', 'ar'] as $locale) {
        foreach (['/', '/customer/login', '/customer/register'] as $path) {
            $this->get($path.'?locale='.$locale)->assertOk()
                ->assertSee('href="'.Storage::url($record->value).'"', false);
        }
    }
});

it('replaces the favicon with a new URL and clears the response cache', function () {
    $this->loginAsAdmin();
    Storage::put('configuration/old.png', 'icon');
    CoreConfig::create(['code' => 'general.design.admin_logo.favicon', 'value' => 'configuration/old.png']);
    ResponseCache::shouldReceive('clear')->once();

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => ['favicon' => UploadedFile::fake()->image('new.png', 32, 32)]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $path = CoreConfig::where('code', 'general.design.admin_logo.favicon')->firstOrFail()->value;
    Storage::assertMissing('configuration/old.png');
    Storage::assertExists($path);
    expect($path)->not->toBe('configuration/old.png');
});

it('restores the channel favicon when the design favicon is removed', function () {
    $this->loginAsAdmin();
    Storage::put('channels/icon.ico', 'icon');
    Storage::put('configuration/site.png', 'icon');
    $this->channel->forceFill(['favicon' => 'channels/icon.ico'])->save();
    core()->setCurrentChannel($this->channel->fresh());
    CoreConfig::create(['code' => 'general.design.admin_logo.favicon', 'value' => 'configuration/site.png']);

    $this->post($this->url, array_merge($this->payload, [
        'general' => ['design' => ['admin_logo' => ['favicon' => ['delete' => 1]]]],
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $this->assertDatabaseMissing('core_config', ['code' => 'general.design.admin_logo.favicon']);
    $this->get('/')->assertOk()->assertSee('href="'.Storage::url('channels/icon.ico').'"', false);
});
