<?php

use Illuminate\Support\Arr;
use Webkul\Shop\Helpers\HomeContent;

it('has matching and complete home translations in French English and Arabic', function () {
    $expectedShopKeys = array_keys(Arr::dot(require base_path('packages/Louzir/Shop/src/Resources/lang/en/home.php')));
    $expectedAdminKeys = array_keys(Arr::dot(require base_path('packages/Louzir/Admin/src/Resources/lang/en/louzir-home.php')));

    foreach (['fr', 'en', 'ar'] as $locale) {
        $shop = Arr::dot(require base_path('packages/Louzir/Shop/src/Resources/lang/'.$locale.'/home.php'));
        $admin = Arr::dot(require base_path('packages/Louzir/Admin/src/Resources/lang/'.$locale.'/louzir-home.php'));
        expect(array_keys($shop))->toBe($expectedShopKeys)
            ->and(array_keys($admin))->toBe($expectedAdminKeys);

        foreach (array_merge($shop, $admin) as $text) {
            expect(is_string($text) && trim($text) !== '')->toBeTrue();
        }

        if ($locale === 'ar') {
            foreach ($admin as $text) {
                expect(preg_match('/\p{Arabic}/u', $text))->toBe(1);
            }
        }

        foreach (HomeContent::schema() as $group => $fields) {
            expect(trans('admin::louzir-home.sections.'.$group, [], $locale))->not->toBe('admin::louzir-home.sections.'.$group);
            foreach ($fields as $name => $field) {
                $label = 'admin::louzir-home.fields.'.$name;
                expect(trans($label, [], $locale))->not->toBe($label);
                if (isset($field['translation'])) {
                    $key = str_contains($field['translation'], '::') ? $field['translation'] : 'shop::app.home.storefront.'.$field['translation'];
                    expect(app('translator')->hasForLocale($key, $locale))->toBeTrue();
                }
            }
        }
    }
});
