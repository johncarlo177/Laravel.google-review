<?php

namespace App\Providers;

use App\Http\Middleware\CustomDomainServer;
use App\Support\Auth\AuthManager;
use App\Support\ConfigValidation\ConfigValidationManager;
use App\Support\ContentManager;
use App\Support\DropletManager;
use App\Support\FaviconManager;
use App\Support\Mail\PHPMailerTransport;
use App\Support\PageTitle;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use App\Support\PDF\CutContourInjector;
use App\Support\QRCodeTypes\ViewComposers\Components\QRCodeFavicon\FileServer as QRCodeFaviconFileServer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Throwable;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            ini_set('memory_limit', config('app.memory_limit', '256M'));
        } catch (Throwable $th) {
            // 
        }

        Schema::defaultStringLength(191);

        $this->forceHttps();

        $this->bindAppUrl();

        $this->bootMacros();

        $this->app->singleton('pageTitle', function ($app) {
            return new PageTitle();
        });

        $this->app->singleton('contentManager', function () {
            return app(ContentManager::class);
        });

        Paginator::defaultView('pagination/default');

        Paginator::defaultSimpleView('pagination/simple-default');

        JsonResource::withoutWrapping();

        Mail::extend('smtp', function () {
            return new PHPMailerTransport();
        });

        FaviconManager::boot();

        ConfigValidationManager::boot();

        DropletManager::boot();

        QRCodeFaviconFileServer::boot();

        AuthManager::boot();

        PaymentProcessorManager::boot();

        CutContourInjector::clearTestFiles();
    }

    private function bootMacros()
    {
        foreach (glob(base_path('macros/*.php')) as $file) {
            require_once $file;
        }
    }

    protected function shouldForceHttps()
    {
        if (CustomDomainServer::servingCustomDomain()) return false;

        $forwardedProtocol = request()->header('X-Forwarded-Proto');

        $forwardedProtocol = strtoupper($forwardedProtocol);

        // if serving reuqests behind a reverse-proxy with https
        return ($forwardedProtocol === 'HTTPS') ||
            config('app.force_https');
    }

    private function forceHttps()
    {
        if (!$this->shouldForceHttps()) {
            return;
        }

        URL::forceScheme('https');

        $this->app['request']->server->set('HTTPS', true);

        Config::set('app.url', url('/'));
    }

    private function bindAppUrl()
    {
        if (CustomDomainServer::servingCustomDomain()) {
            return;
        }

        if (Config::get('app.url') !== url('/')) {
            Config::set('app.url', url('/'));
        }
    }
}
