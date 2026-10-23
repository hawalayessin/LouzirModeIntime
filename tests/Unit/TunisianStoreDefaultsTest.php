<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('pdo_sqlite')]
class TunisianStoreDefaultsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('direction');
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            foreach (['code', 'name', 'symbol', 'group_separator', 'decimal_separator', 'currency_position'] as $column) {
                $table->string($column)->nullable();
            }
            $table->integer('decimal')->default(2);
        });
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('default_locale_id')->constrained('locales');
            $table->foreignId('base_currency_id')->constrained('currencies');
            $table->string('logo')->nullable();
        });
        foreach (['channel_locales' => ['locale_id', 'locales'], 'channel_currencies' => ['currency_id', 'currencies']] as $name => [$column, $related]) {
            Schema::create($name, function (Blueprint $table) use ($column, $related) {
                $table->foreignId('channel_id')->constrained('channels');
                $table->foreignId($column)->constrained($related);
                $table->primary(['channel_id', $column]);
            });
        }
        foreach (['channel_translations' => 'channel_id', 'category_translations' => 'category_id', 'theme_customization_translations' => 'theme_customization_id', 'cms_page_translations' => 'cms_page_id'] as $name => $key) {
            Schema::create($name, function (Blueprint $table) use ($key) {
                $table->id();
                $table->integer($key);
                $table->string('locale');
                $table->string('name')->nullable();
                $table->text('options')->nullable();
                $table->integer('locale_id')->nullable();
                $table->unique([$key, 'locale']);
            });
        }
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency_code');
            $table->string('order_currency_code');
        });
        Schema::create('core_config', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('value');
            $table->string('channel_code')->nullable();
            $table->string('locale_code')->nullable();
        });
        DB::table('locales')->insert(['id' => 1, 'code' => 'en', 'name' => 'English', 'direction' => 'ltr']);
        DB::table('currencies')->insert(['id' => 1, 'code' => 'USD', 'name' => 'Dollar', 'symbol' => '$']);
        DB::table('channels')->insert(['id' => 1, 'code' => 'default', 'default_locale_id' => 1, 'base_currency_id' => 1, 'logo' => 'merchant-logo.svg']);
        DB::table('channel_locales')->insert(['channel_id' => 1, 'locale_id' => 1]);
        DB::table('channel_currencies')->insert(['channel_id' => 1, 'currency_id' => 1]);
        DB::table('category_translations')->insert(['category_id' => 1, 'locale' => 'en', 'name' => 'Root', 'locale_id' => 1]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function configureStore(): void
    {
        $migration = require dirname(__DIR__, 2).'/database/migrations/2026_10_23_000001_configure_tunisian_store_defaults.php';
        $migration->up();
    }

    public function test_it_switches_an_english_dollar_store_to_french_and_tnd(): void
    {
        $this->configureStore();
        $channel = DB::table('channels')->first();
        $this->assertSame('fr', DB::table('locales')->where('id', $channel->default_locale_id)->value('code'));
        $this->assertSame('TND', DB::table('currencies')->where('id', $channel->base_currency_id)->value('code'));
        $this->assertEqualsCanonicalizing(['fr', 'en', 'ar'], DB::table('locales')->pluck('code')->all());
        $this->assertSame(3, DB::table('currencies')->where('code', 'TND')->value('decimal'));
        $this->assertSame('merchant-logo.svg', $channel->logo);
        $this->assertFalse(DB::table('currencies')->where('code', 'USD')->exists());
        $this->assertSame(1, DB::table('channel_currencies')->count());
    }

    public function test_it_is_repeatable_and_preserves_merchant_translations(): void
    {
        DB::table('category_translations')->insert(['category_id' => 1, 'locale' => 'fr', 'name' => 'Ma collection']);
        $this->configureStore();
        $this->configureStore();
        $this->assertSame(3, DB::table('category_translations')->count());
        $this->assertSame('Ma collection', DB::table('category_translations')->where('locale', 'fr')->value('name'));
        $this->assertSame(3, DB::table('channel_locales')->count());
        $this->assertSame(1, DB::table('currencies')->count());
    }

    public function test_it_keeps_usd_for_historical_orders_but_removes_it_from_the_store(): void
    {
        DB::table('orders')->insert(['base_currency_code' => 'USD', 'order_currency_code' => 'USD']);
        $this->configureStore();
        $this->assertTrue(DB::table('currencies')->where('code', 'USD')->exists());
        $enabled = DB::table('currencies')->join('channel_currencies', 'currencies.id', '=', 'currency_id')->pluck('code')->all();
        $this->assertSame(['TND'], $enabled);
    }

    public function test_it_translates_default_footer_labels_without_replacing_custom_content(): void
    {
        DB::table('theme_customization_translations')->insert([
            'theme_customization_id' => 1,
            'locale' => 'en',
            'options' => json_encode(['links' => [['title' => 'About Us'], ['title' => 'My custom link']]]),
        ]);
        $this->configureStore();
        $options = json_decode(DB::table('theme_customization_translations')->where('locale', 'fr')->value('options'), true);
        $this->assertSame(trans('installer::app.seeders.shop.theme-customizations.footer-links.options.about-us', [], 'fr'), $options['links'][0]['title']);
        $this->assertSame('My custom link', $options['links'][1]['title']);
    }

    public function test_it_localizes_the_default_offer_without_changing_the_discount(): void
    {
        DB::table('core_config')->insert(['code' => 'general.content.header_offer.title', 'value' => 'Get UPTO 50% OFF on your 1st order']);
        $this->configureStore();
        $title = DB::table('core_config')->where('code', 'general.content.header_offer.title')->where('locale_code', 'fr')->value('value');
        $this->assertSame('Jusqu’à 50 % de réduction sur votre première commande', $title);
    }
}
