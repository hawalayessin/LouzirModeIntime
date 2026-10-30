<?php

namespace Webkul\Shop\Helpers;

final class StorefrontUrl
{
    /** Keep installer links portable without changing merchant destinations. */
    public static function normalize(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false
            || ! in_array(strtolower($parts['host'] ?? ''), ['localhost', '127.0.0.1', '[::1]'], true)
            || ! in_array(strtolower($parts['scheme'] ?? 'http'), ['http', 'https'], true)) {
            return $url;
        }

        return '/'.ltrim($parts['path'] ?? '', '/')
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}
