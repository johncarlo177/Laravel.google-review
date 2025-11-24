<?php

namespace App\Providers;

use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider as Base;

class ViewServiceProvider extends Base
{
    public function registerViewFinder()
    {
        $this->app->singleton('view.finder', function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        });
    }
}
