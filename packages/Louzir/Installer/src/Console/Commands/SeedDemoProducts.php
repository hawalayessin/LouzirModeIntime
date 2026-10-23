<?php

namespace Webkul\Installer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Webkul\Installer\Database\Seeders\ProductTableSeeder;
use Webkul\Installer\Helpers\DatabaseManager;

class SeedDemoProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bagisto:seed-products
        {--locale= : Locale used for the product translations}
        {--locales=* : Extra locales to seed the product translations for}
        {--force : Run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the demo catalog defined in demo-products.json.';

    /**
     * Tables that must be migrated before the demo catalog can be seeded.
     *
     * @var string[]
     */
    protected $requiredTables = [
        'products',
        'categories',
        'channels',
        'attributes',
        'attribute_families',
    ];

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManager $databaseManager): int
    {
        if (! $databaseManager->checkDatabaseConnection()) {
            $this->error('Unable to connect to the database. Review the DB_* variables in your `.env` file.');

            return self::FAILURE;
        }

        if ($error = $this->missingPrerequisite()) {
            $this->error($error);

            return self::FAILURE;
        }

        if (! $this->confirmDestructiveRun()) {
            return self::SUCCESS;
        }

        $parameters = [
            'default_locale' => $this->option('locale') ?: config('app.locale'),
            'allowed_locales' => $this->resolveLocales(),
            'default_currency' => config('app.currency'),
            'allowed_currencies' => [config('app.currency')],
        ];

        $this->components->info('Seeding demo products for locales: '.implode(', ', $parameters['allowed_locales']));

        $startedAt = microtime(true);

        try {
            app(ProductTableSeeder::class)->run($parameters);
        } catch (Throwable $e) {
            report($e);

            $this->error('Seeding failed: '.$e->getMessage());
            $this->line('See `storage/logs/laravel.log` for the full stack trace.');

            return self::FAILURE;
        }

        $this->components->info('Indexing the catalog...');

        $exitCode = Artisan::call('indexer:index', ['--mode' => ['full'], '--quiet' => true]);

        if ($exitCode !== 0) {
            $this->warn('Indexing returned an error. Run `php artisan indexer:index --mode full` manually.');
        }

        $this->components->success(sprintf(
            '%d demo product(s) seeded in %.1f seconds.',
            DB::table('products')->count(),
            microtime(true) - $startedAt
        ));

        return self::SUCCESS;
    }

    /**
     * Ask for confirmation before replacing the existing catalog.
     */
    protected function confirmDestructiveRun(): bool
    {
        $existingProducts = DB::table('products')->count();

        if ($existingProducts === 0 || $this->option('force')) {
            return true;
        }

        if (! $this->confirm("The {$existingProducts} existing product(s) will be deleted and replaced by the demo catalog. Continue?")) {
            $this->comment('Aborted.');

            return false;
        }

        return true;
    }

    /**
     * Verify that the base installation data is present.
     */
    protected function missingPrerequisite(): ?string
    {
        foreach ($this->requiredTables as $table) {
            if (! Schema::hasTable($table)) {
                return "The `{$table}` table is missing. Run `php artisan migrate` and `php artisan db:seed` first.";
            }
        }

        if (! DB::table('categories')->find(1)) {
            return 'The root category is missing. Run `php artisan db:seed` first.';
        }

        if (! DB::table('channels')->find(1)) {
            return 'The default channel is missing. Run `php artisan db:seed` first.';
        }

        return null;
    }

    /**
     * Resolve the locales the product translations are seeded for.
     *
     * @return string[]
     */
    protected function resolveLocales(): array
    {
        $defaultLocale = $this->option('locale') ?: config('app.locale');

        $channelLocales = [];

        if (Schema::hasTable('channel_locales') && Schema::hasTable('locales')) {
            $channelLocales = DB::table('channel_locales')
                ->join('locales', 'locales.id', '=', 'channel_locales.locale_id')
                ->distinct()
                ->pluck('code')
                ->all();
        }

        return array_values(array_unique(array_merge(
            [$defaultLocale],
            $this->option('locales'),
            $channelLocales
        )));
    }
}
