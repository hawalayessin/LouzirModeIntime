<?php

namespace Webkul\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Core\Repositories\LocaleRepository;

class Locale
{
    /**
     * Create a middleware instance.
     *
     * @return void
     */
    public function __construct(protected LocaleRepository $localeRepository) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $channel = core()->getCurrentChannel();
        $locales = $channel->locales->pluck('code')->toArray();
        $defaultLocaleCode = $channel->default_locale->code;
        $requestedLocaleCode = core()->getRequestedLocaleCode('locale', false);
        $sessionLocaleCode = session()->get('locale');
        $defaultLocaleSignature = $channel->id.':'.$defaultLocaleCode.':1';
        $defaultLocaleChanged = session()->get('locale_default_signature') !== $defaultLocaleSignature;

        if ($requestedLocaleCode && in_array($requestedLocaleCode, $locales)) {
            $localeCode = $requestedLocaleCode;
        } elseif (! $defaultLocaleChanged && in_array($sessionLocaleCode, $locales)) {
            $localeCode = $sessionLocaleCode;
        } else {
            $localeCode = $defaultLocaleCode;
        }

        app()->setLocale($localeCode);
        session()->put('locale', $localeCode);
        session()->put('locale_default_signature', $defaultLocaleSignature);
        unset($request['locale']);

        return $next($request);
    }
}
