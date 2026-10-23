<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $schema = require __DIR__.'/../../packages/Louzir/Shop/src/Config/home.php';
        $codes = [];

        foreach ($schema as $group => $fields) {
            foreach ($fields as $name => $field) {
                if (isset($field['translation'])) {
                    $codes[] = 'general.louzir_home.'.$group.'.'.$name;
                }
            }
        }

        // The shared editor stored English content. Keep the original rows as a backup.
        DB::transaction(function () use ($codes) {
            $records = DB::table('core_config')
                ->whereIn('code', $codes)
                ->whereNull('locale_code')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();

            foreach ($records as $record) {
                $exists = DB::table('core_config')
                    ->where('code', $record->code)
                    ->where('channel_code', $record->channel_code)
                    ->where('locale_code', 'en')
                    ->exists();

                if (! $exists) {
                    $copy = (array) $record;
                    unset($copy['id']);
                    $copy['locale_code'] = 'en';
                    DB::table('core_config')->insert($copy);
                }
            }
        });
    }

    public function down(): void
    {
        // Keep localized content: it may have been edited since the migration.
    }
};
