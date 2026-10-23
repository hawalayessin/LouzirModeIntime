<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('pdo_sqlite')]
class AuthFormsConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'repository.cache.enabled' => false]);
        DB::purge('sqlite');
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('theme')->default('default');
        });
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code');
        });
        Schema::create('channel_locales', function (Blueprint $table) {
            $table->integer('channel_id');
            $table->integer('locale_id');
        });
        Schema::create('core_config', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->text('value');
            $table->string('channel_code')->nullable();
            $table->string('locale_code')->nullable();
            $table->timestamps();
        });
        DB::table('channels')->insert(['id' => 1, 'code' => 'default']);
        foreach (['fr', 'en', 'ar'] as $index => $locale) {
            DB::table('locales')->insert(['id' => $index + 1, 'code' => $locale]);
            DB::table('channel_locales')->insert(['channel_id' => 1, 'locale_id' => $index + 1]);
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function migrateAgreement(): void
    {
        $migration = require dirname(__DIR__, 2).'/database/migrations/2026_10_23_000002_enable_default_auth_agreement.php';
        $migration->up();
    }

    public function test_new_databases_enable_the_auth_agreement_without_manual_admin_setup(): void
    {
        foreach (['fr', 'en', 'ar'] as $locale) {
            $this->assertTrue((bool) core()->getConfigData('general.gdpr.settings.enabled', 'default', $locale));
            $this->assertTrue((bool) core()->getConfigData('general.gdpr.agreement.enabled', 'default', $locale));
        }
    }

    public function test_migration_initializes_all_channel_languages_and_is_repeatable(): void
    {
        $this->migrateAgreement();
        $this->migrateAgreement();
        $this->assertSame(6, DB::table('core_config')->count());
        $this->assertSame(6, DB::table('core_config')->where('value', '1')->count());
    }

    public function test_explicit_administrator_choices_and_labels_are_preserved(): void
    {
        DB::table('core_config')->insert([
            ['code' => 'general.gdpr.agreement.enabled', 'value' => '0', 'channel_code' => 'default', 'locale_code' => 'fr'],
            ['code' => 'general.gdpr.agreement.agreement_label', 'value' => 'Mon libellé personnalisé', 'channel_code' => 'default', 'locale_code' => 'fr'],
        ]);
        $this->migrateAgreement();
        $this->assertFalse((bool) core()->getConfigData('general.gdpr.agreement.enabled', 'default', 'fr'));
        $this->assertTrue((bool) core()->getConfigData('general.gdpr.agreement.enabled', 'default', 'en'));
        $this->assertSame('Mon libellé personnalisé', core()->getConfigData('general.gdpr.agreement.agreement_label', 'default', 'fr'));
    }

    public function test_diagnostic_detects_a_login_view_overridden_by_the_active_theme(): void
    {
        $relative = 'storage/framework/testing/auth-view-'.bin2hex(random_bytes(8));
        $directory = base_path($relative);
        mkdir($directory.'/customers', 0777, true);
        $fixture = $directory.'/customers/sign-in.blade.php';
        file_put_contents($fixture, 'Custom login view');
        config(['themes.shop.default.views_path' => $relative]);

        try {
            $this->artisan('louzir:auth-status')
                ->expectsOutput('Vue surchargée par le thème : sign-in')
                ->expectsOutput('default / fr : conditions affichées')
                ->assertSuccessful();
        } finally {
            unlink($fixture);
            rmdir($directory.'/customers');
            rmdir($directory);
        }
    }
}
