<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $accounts = DB::table('core_config')
                ->where('code', 'sales.payment_methods.paypal_standard.business_account')
                ->whereRaw('LOWER(TRIM(value)) = ?', ['test@webkul.com'])
                ->get(['id', 'channel_code']);

            foreach ($accounts as $account) {
                DB::table('core_config')->where('id', $account->id)->update(['value' => '']);
                DB::table('core_config')
                    ->where('code', 'sales.payment_methods.paypal_standard.active')
                    ->where('channel_code', $account->channel_code)
                    ->update(['value' => '0']);
            }
        });
    }

    public function down(): void
    {
        // Do not restore a third-party demo payment recipient.
    }
};
