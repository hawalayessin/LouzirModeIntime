<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductAttributeLabelsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (DB::table('attributes')->where('is_user_defined', 0)->get() as $attribute) {
                $key = 'installer::app.seeders.attribute.attributes.'.str_replace('_', '-', $attribute->code);
                foreach (DB::table('locales')->whereIn('code', ['fr', 'en', 'ar'])->pluck('code') as $locale) {
                    $scope = ['attribute_id' => $attribute->id, 'locale' => $locale];
                    if (DB::table('attribute_translations')->where($scope)->whereNotNull('name')->where('name', '<>', '')->exists()) {
                        continue;
                    }
                    $label = trans($key, [], $locale);
                    DB::table('attribute_translations')->updateOrInsert($scope, [
                        'name' => $label === $key ? $attribute->admin_name : $label,
                    ]);
                }
            }
        });
    }
}
