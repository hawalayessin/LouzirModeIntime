<?php

namespace Webkul\Shop\Helpers;

use Illuminate\Support\Facades\Storage;
use Webkul\Core\Repositories\CoreConfigRepository;

class HomeContent
{
    private array $values = [];

    public static function schema(): array
    {
        static $schema = null;

        return $schema ??= require __DIR__.'/../Config/home.php';
    }

    public function value(string $name, ?string $channel = null, ?string $locale = null): string
    {
        $channel ??= core()->getCurrentChannelCode();
        $locale ??= app()->getLocale();
        $scope = $channel;

        $this->values[$scope] ??= app(CoreConfigRepository::class)->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->findWhere([
            ['code', 'like', 'general.louzir_home.%'],
            'channel_code' => $channel,
        ]);

        foreach (self::schema() as $group => $fields) {
            if (! isset($fields[$name])) {
                continue;
            }

            $field = $fields[$name];
            $records = $this->values[$scope]->where('code', 'general.louzir_home.'.$group.'.'.$name);
            $record = isset($field['translation'])
                ? $records->firstWhere('locale_code', $locale)
                : ($records->firstWhere('locale_code', null) ?? $records->first());
            $value = $record?->value;
            $fallback = isset($field['translation'])
                ? trans('shop::app.home.storefront.'.$field['translation'], [], $locale)
                : $field['fallback'];

            if ($field['type'] === 'image') {
                return $value ? Storage::url($value) : asset($fallback);
            }

            $value = $value === null || $value === '' ? $fallback : $value;

            if (in_array($field['type'], ['link', 'map']) && ! self::validUrl($value, $field['type'] === 'map')) {
                return $fallback;
            }

            return $value;
        }

        return '';
    }

    public static function validUrl(string $value, bool $map = false): bool
    {
        if ($value === '') {
            return true;
        }

        if (preg_match('/[\\x00-\\x20\\x7f\\\\\\\\]/', $value)) {
            return false;
        }

        if ($map) {
            $parts = parse_url($value);

            return $parts
                && ($parts['scheme'] ?? '') === 'https'
                && in_array($parts['host'] ?? '', ['maps.google.com', 'www.google.com'])
                && str_starts_with($parts['path'] ?? '', '/maps')
                && ! isset($parts['user'])
                && ! isset($parts['pass']);
        }

        return preg_match('~^(?:https?://[^/]+|/(?!/)|#[^#]|tel:\\+?[0-9]|mailto:[^@]+@)~i', $value) === 1;
    }
}
