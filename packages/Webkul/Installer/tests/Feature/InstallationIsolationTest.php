<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Installer\Helpers\DatabaseManager;
use Webkul\Installer\Http\Middleware\CanInstall;

it('does not register the vendor installation notification listener', function () {
    expect(Event::hasListeners('bagisto.installed'))->toBeFalse()
        ->and(class_exists('Webkul\\Installer\\Listeners\\Installer'))->toBeFalse();
});

it('recreates the local installation marker without dispatching installation notifications', function () {
    Event::fake();
    $original = storage_path();
    $temporary = storage_path('framework/testing/installer-isolation-'.bin2hex(random_bytes(8)));
    mkdir($temporary, 0777, true);
    app()->useStoragePath($temporary);
    $manager = Mockery::mock(DatabaseManager::class);
    $manager->shouldReceive('isInstalled')->once()->andReturnTrue();
    app()->instance(DatabaseManager::class, $manager);

    try {
        expect((new CanInstall)->isAlreadyInstalled())->toBeTrue()
            ->and(is_file(storage_path('installed')))->toBeTrue();
        Event::assertNothingDispatched();
    } finally {
        app()->useStoragePath($original);
        unlink($temporary.'/installed');
        rmdir($temporary);
    }
});

it('blocks every web installer endpoint when installation is disabled', function () {
    config(['installer.enabled' => false]);
    $this->get('/install')->assertNotFound();
    foreach (['run-migration', 'run-seeder', 'create-admin-user', 'seed-sample-products'] as $endpoint) {
        $this->post('/install/api/'.$endpoint)->assertNotFound();
    }
});

it('removes stored vendor demo payment settings without altering a merchants own account', function () {
    $prefix = 'sales.payment_methods.paypal_standard.';
    DB::table('core_config')->where('code', 'like', $prefix.'%')->delete();
    DB::table('core_config')->insert([
        ['code' => $prefix.'business_account', 'value' => ' Test@Webkul.com ', 'channel_code' => 'default', 'locale_code' => null],
        ['code' => $prefix.'active', 'value' => '1', 'channel_code' => 'default', 'locale_code' => null],
        ['code' => $prefix.'business_account', 'value' => 'merchant@example.com', 'channel_code' => 'own', 'locale_code' => null],
        ['code' => $prefix.'active', 'value' => '1', 'channel_code' => 'own', 'locale_code' => null],
    ]);
    $migration = require base_path('database/migrations/2026_10_21_000002_remove_vendor_demo_payment_account.php');
    $migration->up();
    $migration->up();
    expect(DB::table('core_config')->where('code', $prefix.'business_account')->where('channel_code', 'default')->value('value'))->toBe('')
        ->and(DB::table('core_config')->where('code', $prefix.'active')->where('channel_code', 'default')->value('value'))->toBe('0')
        ->and(DB::table('core_config')->where('code', $prefix.'business_account')->where('channel_code', 'own')->value('value'))->toBe('merchant@example.com')
        ->and(DB::table('core_config')->where('code', $prefix.'active')->where('channel_code', 'own')->value('value'))->toBe('1');

    $defaults = require base_path('packages/Webkul/Paypal/src/Config/payment-methods.php');
    expect($defaults['paypal_standard']['business_account'])->toBe('');
});
