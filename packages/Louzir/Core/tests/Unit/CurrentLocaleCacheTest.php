<?php

use Webkul\Core\Models\Locale;

it('refreshes the current locale when the application language changes', function () {
    foreach (['en', 'fr', 'ar'] as $locale) {
        Locale::firstOrCreate(['code' => $locale], ['name' => $locale, 'direction' => $locale === 'ar' ? 'rtl' : 'ltr']);
    }

    foreach (['fr', 'en', 'ar', 'fr'] as $locale) {
        app()->setLocale($locale);
        expect(core()->getCurrentLocale()->code)->toBe($locale);
    }
});
