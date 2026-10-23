<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AuthFormsStatus extends Command
{
    protected $signature = 'louzir:auth-status';

    protected $description = 'Affiche les vues réellement utilisées et les réglages des formulaires clients.';

    public function handle(): int
    {
        $channel = core()->getDefaultChannel();
        $theme = $channel->theme ?: config('themes.shop-default');
        themes()->set(themes()->exists($theme) ? $theme : config('themes.shop-default'));

        $rows = [];
        foreach (['sign-in', 'sign-up', 'auth-styles', 'agreement', 'password-toggle'] as $name) {
            $path = view()->getFinder()->find('shop::customers.'.$name);
            $expected = base_path('packages/Louzir/Shop/src/Resources/views/customers/'.$name.'.blade.php');
            $rows[] = [$name, $path, hash_file('sha256', $path)];
            if (realpath($path) !== realpath($expected)) {
                $this->warn('Vue surchargée par le thème : '.$name);
            }
        }
        $this->table(['Vue', 'Fichier chargé', 'SHA-256'], $rows);

        foreach ($channel->locales as $locale) {
            $enabled = core()->getConfigData('general.gdpr.settings.enabled', $channel->code, $locale->code)
                && core()->getConfigData('general.gdpr.agreement.enabled', $channel->code, $locale->code);
            $this->line($channel->code.' / '.$locale->code.' : conditions '.($enabled ? 'affichées' : 'désactivées dans la configuration'));
        }

        return self::SUCCESS;
    }
}
