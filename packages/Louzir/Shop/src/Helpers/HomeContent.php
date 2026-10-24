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
                ? trans(str_contains($field['translation'], '::') ? $field['translation'] : 'shop::app.home.storefront.'.$field['translation'], [], $locale)
                : $field['fallback'];

            if ($field['type'] === 'image') {
                return $value && Storage::exists($value) ? Storage::url($value) : asset($fallback);
            }

            $value = $value === null || $value === '' ? $fallback : $value;

            if ($field['type'] === 'boolean') {
                return in_array((string) $value, ['0', '1'], true) ? (string) $value : $fallback;
            }

            if ($field['type'] === 'color') {
                return preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1 ? $value : $fallback;
            }

            if ($field['type'] === 'number') {
                $number = filter_var($value, FILTER_VALIDATE_INT);

                return (string) max($field['min'], min($field['max'], $number === false ? (int) $fallback : $number));
            }

            if ($field['type'] === 'select' && ! in_array($value, $field['options'], true)) {
                return $fallback;
            }

            if (in_array($field['type'], ['link', 'map']) && ! self::validUrl($value, $field['type'] === 'map')) {
                return $fallback;
            }

            return $value;
        }

        return '';
    }

    /** Return enabled slides in editorial order, preserving the original hero settings. */
    public function heroSlides(): array
    {
        $slides = [];

        if ($this->value('hero-slide-1-enabled') === '1') {
            $slides[] = [
                'id' => 1,
                'image' => $this->value('image-1'),
                'alt' => $this->value('hero-image-alt'),
                'kicker' => $this->value('hero-kicker'),
                'title' => $this->value('hero-brand-title'),
                'tagline' => trim($this->value('hero-title-start').' '.$this->value('hero-title-emphasis')),
                'description' => $this->value('hero-description'),
                'button' => $this->value('hero-discover'),
                'url' => $this->value('hero-discover-url'),
                'x' => $this->value('hero-position-x'),
                'y' => $this->value('hero-position-y'),
                'mobile_x' => $this->value('hero-mobile-x'),
                'overlay' => $this->value('hero-overlay'),
            ];
        }

        foreach ([2, 3] as $id) {
            if ($this->value('hero-'.$id.'-enabled') !== '1') {
                continue;
            }

            $slide = ['id' => $id];
            foreach (['image', 'alt', 'kicker', 'title', 'tagline', 'description', 'button', 'url'] as $key) {
                $slide[$key] = $this->value('hero-'.$id.'-'.$key);
            }
            $slide['x'] = $this->value('hero-'.$id.'-position-x');
            $slide['y'] = $this->value('hero-'.$id.'-position-y');
            $slide['mobile_x'] = $this->value('hero-'.$id.'-mobile-x');
            $slide['overlay'] = $this->value('hero-'.$id.'-overlay');
            $slides[] = $slide;
        }

        return $slides;
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
