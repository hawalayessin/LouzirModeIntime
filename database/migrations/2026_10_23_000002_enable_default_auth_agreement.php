<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $contexts = DB::table('channel_locales')
                ->join('channels', 'channels.id', '=', 'channel_locales.channel_id')
                ->join('locales', 'locales.id', '=', 'channel_locales.locale_id')
                ->get(['channels.code as channel', 'locales.code as locale']);

            foreach ($contexts as $context) {
                foreach (['general.gdpr.settings.enabled', 'general.gdpr.agreement.enabled'] as $code) {
                    $exists = DB::table('core_config')->where('code', $code)
                        ->where(fn ($query) => $query->whereNull('channel_code')->orWhere('channel_code', $context->channel))
                        ->where(fn ($query) => $query->whereNull('locale_code')->orWhere('locale_code', $context->locale))
                        ->exists();

                    // Existing administrator choices, including disabled agreements, remain authoritative.
                    if (! $exists) {
                        DB::table('core_config')->insert([
                            'code' => $code,
                            'value' => '1',
                            'channel_code' => $context->channel,
                            'locale_code' => $context->locale,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Preserve administrator settings when rolling back application code.
    }
};
