<?php

namespace Webkul\Installer\Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\Installer\Helpers\DatabaseManager;
use Webkul\Installer\Http\Middleware\CanInstall;

class ProductionInstallerTest extends TestCase
{
    public function test_disabled_installer_blocks_pages_and_write_apis(): void
    {
        config(['installer.enabled' => false]);
        foreach (['/install', '/install/api/run-migration', '/install/api/run-seeder'] as $path) {
            $response = (new CanInstall)->handle(Request::create($path), fn () => response('Allowed'));
            $this->assertSame(404, $response->getStatusCode());
        }
    }

    public function test_disabled_installer_does_not_redirect_storefront(): void
    {
        config(['installer.enabled' => false]);
        $response = (new CanInstall)->handle(Request::create('/'), fn () => response('Storefront'));
        $this->assertSame('Storefront', $response->getContent());
    }

    public function test_installation_detection_does_not_require_env_file(): void
    {
        app()->loadEnvironmentFrom('.env.nonexistent-installer-test');
        $connection = \Mockery::mock();
        $connection->shouldReceive('getPDO')->once();
        $connection->shouldReceive('getDatabaseName')->once()->andReturn('beta');
        DB::shouldReceive('connection')->twice()->andReturn($connection);
        $schema = \Mockery::mock();
        $schema->shouldReceive('hasTable')->with('admins')->once()->andReturn(true);
        Schema::swap($schema);
        DB::shouldReceive('table->count')->once()->andReturn(1);
        $this->assertTrue(app(DatabaseManager::class)->isInstalled());
    }
}
