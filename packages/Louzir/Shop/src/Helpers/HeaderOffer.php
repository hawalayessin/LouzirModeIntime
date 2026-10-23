<?php

namespace Webkul\Shop\Helpers;

class HeaderOffer
{
    public function colors(): array
    {
        $value = core()->getConfigData('general.content.header_offer.background_color');
        $background = is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)
            ? $value : '#1a1a1a';

        $rgb = array_map(function ($hex) {
            $value = hexdec($hex) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($background, 1), 2));

        $luminance = 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];

        return ['background' => $background, 'text' => $luminance > 0.179 ? '#000000' : '#ffffff'];
    }
}
