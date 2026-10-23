<?php

use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Core\SystemConfig as CoreSystemConfig;

it('hides analytics and diagnostics items from the system configuration listing', function () {
    $original = config('core');

    config()->set('core', [
        [
            'key' => 'general',
            'name' => 'General',
            'sort' => 1,
            'fields' => [],
        ],
        [
            'key' => 'analytics',
            'name' => 'Analytics',
            'sort' => 2,
            'fields' => [],
        ],
        [
            'key' => 'diagnostics',
            'name' => 'Diagnostics',
            'sort' => 3,
            'fields' => [],
        ],
    ]);

    try {
        $systemConfig = new CoreSystemConfig(app(CoreConfigRepository::class));

        $keys = $systemConfig->getItems()->pluck('key')->all();

        expect($keys)
            ->toContain('general')
            ->not->toContain('analytics')
            ->not->toContain('diagnostics');
    } finally {
        config()->set('core', $original);
    }
});
