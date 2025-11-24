<?php

namespace App\Providers;

use App\Support\QRCodeTypes\ViewComposers\Manager as QRCodeTypeViewComposerManager;
use App\Support\ViewComposers\LeadForm\LeadFormComposerManager;
use App\Support\ViewComposers\StackComponent\Composer;
use App\Support\ViewComposers\ViewComposerManager;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
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
        ViewComposerManager::boot();
        QRCodeTypeViewComposerManager::boot();
        LeadFormComposerManager::boot();
        Composer::register();
    }
}
