<?php

namespace Webkul\Shop\Helpers;

final class StorefrontText
{
    /** Translate only known installer defaults; merchant copy remains unchanged. */
    public static function legacyDefault(?string $value): string
    {
        $keys = [
            'Cookie Block' => 'cookie-title',
            'This website uses cookies to ensure you get the best experience on our website.' => 'cookie-description',
            'About Us' => 'about-us',
            'Contact Us' => 'contact-us',
            'Customer Service' => 'customer-service',
            "What's New" => 'whats-new',
            'Terms of Use' => 'terms-use',
            'Terms & Conditions' => 'terms-conditions',
        ];
        $key = $keys[trim($value ?? '')] ?? null;

        return $key ? trans('shop::storefront.'.$key) : ($value ?? '');
    }
}
