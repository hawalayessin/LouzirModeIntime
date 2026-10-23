<?php

use Webkul\Core\Facades\Core;

it('localizes the default agreement label', function (string $locale) {
    app()->setLocale($locale);

    Core::shouldReceive('getConfigData')
        ->with('general.gdpr.agreement.agreement_label')
        ->once()
        ->andReturn('I agree with the terms and conditions.');

    $html = view('shop::customers.agreement')->render();

    expect($html)->toContain(e(trans('shop::auth.agreement')))
        ->toContain('rules="required"')
        ->toContain('name="agreement"');
})->with(['fr', 'en', 'ar']);

it('preserves and escapes the admin agreement label', function () {
    Core::shouldReceive('getConfigData')
        ->with('general.gdpr.agreement.agreement_label')
        ->once()
        ->andReturn('Conditions <personnalisees>');

    expect(view('shop::customers.agreement')->render())
        ->toContain('Conditions &lt;personnalisees&gt;')
        ->not->toContain('Conditions <personnalisees>');
});
