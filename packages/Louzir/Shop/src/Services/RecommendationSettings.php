<?php

namespace Webkul\Shop\Services;

final class RecommendationSettings
{
    public const PREFIX = 'catalog.products.product_view_page.';

    public const MAX_RESULTS = 12;

    public function __construct(
        public readonly int $limit,
        public readonly int $upSellLimit,
        public readonly string $mode,
        public readonly bool $onlySaleable,
        public readonly int $cacheSeconds,
    ) {}

    public static function current(): self
    {
        $get = fn (string $key) => core()->getConfigData(self::PREFIX.$key);
        $mode = $get('related_mode');

        return new self(
            self::bounded($get('no_of_related_products'), 6, 0, self::MAX_RESULTS),
            self::bounded($get('no_of_up_sells_products'), 4, 0, self::MAX_RESULTS),
            in_array($mode, ['hybrid', 'category', 'manual'], true) ? $mode : 'hybrid',
            (string) ($get('related_only_saleable') ?? '1') !== '0',
            self::bounded($get('related_cache_seconds'), 300, 30, 1800),
        );
    }

    public static function rules(): array
    {
        return [
            'no_of_related_products' => ['required', 'integer', 'min:0', 'max:'.self::MAX_RESULTS],
            'no_of_up_sells_products' => ['required', 'integer', 'min:0', 'max:'.self::MAX_RESULTS],
            'related_mode' => ['required', 'in:hybrid,category,manual'],
            'related_only_saleable' => ['required', 'boolean'],
            'related_cache_seconds' => ['required', 'integer', 'min:30', 'max:1800'],
        ];
    }

    private static function bounded(mixed $value, int $default, int $min, int $max): int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT);

        return max($min, min($max, $number === false ? $default : $number));
    }
}
