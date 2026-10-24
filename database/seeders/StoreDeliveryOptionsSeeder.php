<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreDeliveryOptionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (DB::table('channels')->get() as $channel) {
                foreach (['flatrate', 'express'] as $carrier) {
                    foreach (['active' => '1', 'default_rate' => '8', 'type' => 'per_order'] as $field => $value) {
                        $this->putIfMissing('sales.carriers.'.$carrier.'.'.$field, $value, $channel->code);
                    }
                    $titles = $carrier === 'express'
                        ? ['fr' => 'Livraison express', 'en' => 'Express delivery', 'ar' => 'التوصيل السريع']
                        : ['fr' => 'Livraison normale', 'en' => 'Standard delivery', 'ar' => 'التوصيل العادي'];
                    foreach ($titles as $locale => $title) {
                        $this->putIfMissing('sales.carriers.'.$carrier.'.title', $title, $channel->code, $locale);
                        $this->putIfMissing('sales.carriers.'.$carrier.'.description', $title, $channel->code, $locale);
                    }
                }
                $this->putIfMissing('sales.carriers.free.active', '0', $channel->code);
            }
        });
    }

    private function putIfMissing(string $code, string $value, string $channel, ?string $locale = null): void
    {
        $scope = ['code' => $code, 'channel_code' => $channel, 'locale_code' => $locale];
        if (! DB::table('core_config')->where($scope)->exists()) {
            DB::table('core_config')->insert($scope + ['value' => $value, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
