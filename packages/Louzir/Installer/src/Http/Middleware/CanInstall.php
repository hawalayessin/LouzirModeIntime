<?php

namespace Webkul\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Installer\Helpers\DatabaseManager;

class CanInstall
{
    /**
     * Handles Requests for Installer middleware.
     *
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {
        $isInstallerRequest = $request->is('install', 'install/*');

        if (! config('installer.enabled')) {
            return $isInstallerRequest
                ? response('Not Found', 404)
                : $next($request);
        }

        if ($isInstallerRequest) {
            if ($this->isAlreadyInstalled()) {
                if (! $request->ajax()) {
                    return redirect()->route('shop.home.index');
                }

                return response()->json([
                    'message' => trans('installer::app.installer.middleware.already-installed'),
                ], 403);
            }
        } else {
            if (! $this->isAlreadyInstalled()) {
                return redirect()->route('installer.index');
            }
        }

        return $next($request);
    }

    /**
     * Application Already Installed.
     *
     * @return bool
     */
    public function isAlreadyInstalled()
    {
        if (file_exists(storage_path('installed'))) {
            return true;
        }

        if (app(DatabaseManager::class)->isInstalled()) {
            touch(storage_path('installed'));

            return true;
        }

        return false;
    }
}
