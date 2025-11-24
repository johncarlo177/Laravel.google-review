<?php

namespace App\Providers;

use App\Interfaces\CurrencyManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CurrencyServiceProvider extends ServiceProvider
{
    private CurrencyManager $currencies;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('app.installed')) return;

        $this->currencies = app(CurrencyManager::class);

        try {
            Config::set('currency', $this->currencies->enabledCurrency()->toJson());
        } catch (\Throwable $th) {
        }
    }
}
