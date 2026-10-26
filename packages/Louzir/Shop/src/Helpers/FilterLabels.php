<?php

namespace Webkul\Shop\Helpers;

use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;

final class FilterLabels
{
    private const ATTRIBUTES = ['price' => 'Price', 'color' => 'Color', 'size' => 'Size', 'brand' => 'Brand'];

    private const COLORS = ['red' => '#b85a5a', 'green' => '#47725a', 'yellow' => '#d6b753', 'black' => '#252525', 'white' => '#ffffff', 'blue' => '#55719a', 'pink' => '#d6a1ad', 'beige' => '#d6c5ae', 'grey' => '#9a9a9a', 'gray' => '#9a9a9a', 'brown' => '#806454', 'purple' => '#8b709b', 'orange' => '#ce8c55'];

    public static function attribute(Attribute $attribute): string
    {
        $translated = $attribute->translate(app()->getLocale(), false)?->name;
        if (filled($translated)) {
            return $translated;
        }
        $fallback = $attribute->name ?: $attribute->admin_name;

        return isset(self::ATTRIBUTES[$attribute->code]) && strcasecmp($fallback, self::ATTRIBUTES[$attribute->code]) === 0
            ? trans('shop::filters.'.$attribute->code) : $fallback;
    }

    public static function option(AttributeOption $option): string
    {
        $translated = $option->translate(app()->getLocale(), false)?->label;
        if (filled($translated)) {
            return $translated;
        }
        $fallback = $option->label ?: $option->admin_name;
        $key = strtolower(trim($fallback));

        return $option->attribute->code === 'color' && isset(self::COLORS[$key])
            ? trans('shop::filters.colors.'.$key) : $fallback;
    }

    public static function swatch(AttributeOption $option): ?string
    {
        if ($option->attribute->code !== 'color') {
            return null;
        }

        return preg_match('/^#[0-9a-fA-F]{6}$/', $option->swatch_value ?? '')
            ? $option->swatch_value : (self::COLORS[strtolower(trim($option->admin_name))] ?? null);
    }

    public static function colorSearchDefaults(string $query): array
    {
        return array_values(array_filter(array_map('ucfirst', array_keys(self::COLORS)), fn ($name) => mb_stripos(trans('shop::filters.colors.'.strtolower($name)), $query) !== false
        ));
    }
}
