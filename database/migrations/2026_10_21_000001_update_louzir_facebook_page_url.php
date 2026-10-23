<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('core_config')
            ->where('code', 'general.louzir_home.hero.facebook-url')
            ->where('value', 'https://www.facebook.com/search/top/?q=louzir%20mode%20intime&locale=fr_FR')
            ->update(['value' => 'https://www.facebook.com/profile.php?id=61569721747533&locale=fr_FR']);
    }

    public function down(): void
    {
        // Keep the direct page URL instead of restoring the broken search link.
    }
};
