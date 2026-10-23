<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $localeIds = [];

            foreach (['fr' => ['Français', 'ltr'], 'en' => ['English', 'ltr'], 'ar' => ['العربية', 'rtl']] as $code => [$name, $direction]) {
                DB::table('locales')->updateOrInsert(['code' => $code], compact('name', 'direction'));
                $localeIds[$code] = DB::table('locales')->where('code', $code)->value('id');
            }

            DB::table('currencies')->updateOrInsert(['code' => 'TND'], [
                'name' => 'Dinar tunisien',
                'symbol' => 'DT',
                'decimal' => 3,
                'group_separator' => ' ',
                'decimal_separator' => ',',
                'currency_position' => 'right_with_space',
            ]);
            $currencyId = DB::table('currencies')->where('code', 'TND')->value('id');

            foreach (DB::table('channels')->get() as $channel) {
                DB::table('channels')->where('id', $channel->id)->update([
                    'default_locale_id' => $localeIds['fr'],
                    'base_currency_id' => $currencyId,
                ]);
                DB::table('channel_currencies')->where('channel_id', $channel->id)->delete();
                DB::table('channel_currencies')->insert(['channel_id' => $channel->id, 'currency_id' => $currencyId]);
                DB::table('channel_locales')->where('channel_id', $channel->id)->whereNotIn('locale_id', $localeIds)->delete();

                foreach ($localeIds as $localeId) {
                    DB::table('channel_locales')->updateOrInsert(['channel_id' => $channel->id, 'locale_id' => $localeId]);
                }
            }

            // Fill missing translations without replacing content already edited by the merchant.
            foreach ([
                'channel_translations' => 'channel_id',
                'category_translations' => 'category_id',
                'theme_customization_translations' => 'theme_customization_id',
                'cms_page_translations' => 'cms_page_id',
            ] as $table => $foreignKey) {
                foreach (DB::table($table)->get()->groupBy($foreignKey) as $translations) {
                    $source = $translations->firstWhere('locale', 'fr') ?? $translations->firstWhere('locale', 'en') ?? $translations->first();

                    foreach ($localeIds as $code => $localeId) {
                        if ($translations->contains('locale', $code)) {
                            continue;
                        }

                        $copy = (array) $source;
                        unset($copy['id']);
                        $copy['locale'] = $code;
                        if (array_key_exists('locale_id', $copy)) {
                            $copy['locale_id'] = $localeId;
                        }
                        if ($table === 'theme_customization_translations') {
                            $copy['options'] = $this->translateDefaultOptions($copy['options'], $code);
                        }
                        DB::table($table)->insert($copy);
                    }
                }
            }

            foreach (DB::table('core_config')->whereIn('code', ['general.content.header_offer.title', 'general.content.header_offer.redirection_title'])->whereNull('locale_code')->get() as $offer) {
                foreach (array_keys($localeIds) as $locale) {
                    $copy = (array) $offer;
                    unset($copy['id']);
                    $copy['locale_code'] = $locale;
                    if (str_ends_with($offer->code, '.title') && preg_match('/^Get UPTO (\d+)% OFF on your 1st order$/i', $offer->value, $matches)) {
                        $copy['value'] = str_replace('40', $matches[1], trans('shop::app.home.storefront.header-offer-title', [], $locale));
                    } elseif (str_ends_with($offer->code, '.redirection_title') && in_array(strtoupper($offer->value), ['SHOP NOW', 'ACHETER'])) {
                        $copy['value'] = trans('shop::app.home.storefront.header-offer-cta', [], $locale);
                    }
                    if (! DB::table('core_config')->where('code', $offer->code)->where('channel_code', $offer->channel_code)->where('locale_code', $locale)->exists()) {
                        DB::table('core_config')->insert($copy);
                    }
                }
            }

            // Keep the USD definition only if historical orders still need it for display.
            if (! DB::table('orders')->where('base_currency_code', 'USD')->orWhere('order_currency_code', 'USD')->exists()) {
                DB::table('currencies')->where('code', 'USD')->delete();
            }
        });
    }

    private function translateDefaultOptions(string $options, string $locale): string
    {
        $english = trans('installer::app.seeders.shop.theme-customizations.footer-links.options', [], 'en');
        $localized = trans('installer::app.seeders.shop.theme-customizations.footer-links.options', [], $locale);
        $replacements = [];
        foreach ($english as $key => $value) {
            $replacements[$value] = $localized[$key] ?? $value;
        }
        $values = json_decode($options, true, 512, JSON_THROW_ON_ERROR);
        array_walk_recursive($values, function (&$value) use ($replacements) {
            if (is_string($value) && isset($replacements[$value])) {
                $value = $replacements[$value];
            }
        });

        return json_encode($values, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    public function down(): void
    {
        // Keep configured languages, prices and merchant content when rolling back code.
    }
};
