<?php

use Illuminate\Support\Facades\DB;

it('shows the announcement background color picker in the existing content configuration', function () {
    $this->loginAsAdmin();
    $this->get(route('admin.configuration.index', ['general', 'content']))
        ->assertOk()
        ->assertSee('general[content][header_offer][background_color]', false)
        ->assertSee(trans('admin::app.configuration.index.general.content.header-offer.background-color'));
});

it('saves the announcement background color globally', function (string $color) {
    $this->loginAsAdmin();
    $this->postJson(route('admin.configuration.store', ['general', 'content']), [
        'channel' => 'default', 'locale' => 'fr',
        'general' => ['content' => ['header_offer' => ['background_color' => $color]]],
    ])->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseHas('core_config', [
        'code' => 'general.content.header_offer.background_color', 'value' => $color,
        'channel_code' => null, 'locale_code' => null,
    ]);
})->with(['#1a1a1a', '#223344', '#ffffff']);

it('rejects invalid colors even when field metadata is omitted', function (mixed $color) {
    $this->loginAsAdmin();
    $this->postJson(route('admin.configuration.store', ['general', 'content']), [
        'general' => ['content' => ['header_offer' => ['background_color' => $color]]],
    ])->assertUnprocessable()->assertJsonValidationErrors('general.content.header_offer.background_color');
})->with(['red', '#fff', '#1234567', '#12345z', 'red; color: red', [['#ffffff']]]);

it('preserves the configured color when only the offer text is saved', function () {
    DB::table('core_config')->insert([
        'code' => 'general.content.header_offer.background_color', 'value' => '#223344',
    ]);
    $this->loginAsAdmin();
    $this->postJson(route('admin.configuration.store', ['general', 'content']), [
        'channel' => 'default', 'locale' => 'fr',
        'general' => ['content' => ['header_offer' => ['title' => 'Offre de test']]],
    ])->assertRedirect();
    $this->assertDatabaseHas('core_config', ['code' => 'general.content.header_offer.background_color', 'value' => '#223344']);
});
