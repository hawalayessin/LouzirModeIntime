<?php

namespace Webkul\Shop\Services;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RecommendationCache
{
    /** Cache rankings only. Customer prices, availability and resources remain live. */
    public function candidates(array $context, int $seconds, Closure $compute): array
    {
        $namespace = $this->namespace();
        $version = Cache::rememberForever($namespace.':version', fn () => (string) Str::uuid());
        // Keep stable keys: catalog edits overwrite entries instead of accumulating versions on disk.
        $key = $namespace.':v2:'.hash('sha256', json_encode($context, JSON_THROW_ON_ERROR));
        $cached = Cache::get($key);
        if (is_array($cached) && ($cached['version'] ?? null) === $version) {
            return $cached['ids'];
        }

        // Never queue storefront requests behind a simultaneous cold ranking calculation.
        $lock = Cache::lock($key.':lock', 15);
        if (! $lock->get()) {
            $cached = Cache::get($key);

            return ($cached['version'] ?? null) === $version ? $cached['ids'] : [];
        }

        try {
            $cached = Cache::get($key);
            if (($cached['version'] ?? null) === $version) {
                return $cached['ids'];
            }

            $ids = $compute();
            Cache::put($key, ['version' => $version, 'ids' => $ids], $seconds + random_int(0, 30));

            return $ids;
        } finally {
            $lock->release();
        }
    }

    public function invalidate(mixed ...$payload): void
    {
        Cache::forever($this->namespace().':version', (string) Str::uuid());
    }

    private function namespace(): string
    {
        return 'shop:recommendations:'.hash('sha256', DB::connection()->getName().':'.DB::connection()->getDatabaseName());
    }
}
