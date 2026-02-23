<?php

namespace Clearsh\LaravelCollectionExt;

use Clearsh\LaravelCollectionExt\Exceptions\LicenseException;

class LicenseServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/license.php', 'license');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/license.php' => config_path('license.php'),
        ], 'r9x-config');

        if (! $this->app->environment('production')) {
            return;
        }

        if (! LicenseValidator::validate()) {
            throw new LicenseException();
        }

        \Illuminate\Support\Facades\Cache::put('_r9x_grace', true, now()->addDays(3));
    }
}
