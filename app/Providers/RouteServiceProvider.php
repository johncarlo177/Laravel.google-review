<?php

namespace App\Providers;

use App\Http\Controllers\DynamicSlugServer;
use App\Support\Auth\AuthManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Support\PaymentProcessors\WebhookManager as PaymentWebhookManager;

class RouteServiceProvider extends ServiceProvider
{
    public static $loginRouteRegistrar = null;

    private static $apiRoutes = [];
    private static $webRoutes = [];
    private static $webhooksRoutes = [];

    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';


    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'))
                ->group(function () {
                    foreach ($this::$apiRoutes as $route) {
                        call_user_func($route);
                    }
                });

            Route::prefix('webhooks')
                ->namespace($this->namespace)
                ->group(base_path('routes/webhooks.php'))
                ->group(function () {
                    foreach ($this::$webhooksRoutes as $route) {
                        call_user_func($route);
                    }
                })->group(function () {
                    PaymentWebhookManager::init()->defineRoutes();
                });

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'))
                ->group(function () {
                    foreach ($this::$webRoutes as $route) {
                        call_user_func($route);
                    }
                })
                ->group(

                    fn() => AuthManager::registerWebRoutes()

                )->group(

                    fn() => $this->registerDynamicSlugServer()

                );
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    public static function registerApiRoutes($callback)
    {
        static::$apiRoutes[] = $callback;
    }

    public static function registerWebRoutes($callback)
    {
        static::$webRoutes[] = $callback;
    }

    public static function regsiterWebhooksRoutes($callback)
    {
        static::$webhooksRoutes[] = $callback;
    }

    /**
     * Fallback route handler, executed last
     */
    private function registerDynamicSlugServer()
    {
        Route::get(
            '/{slug}',
            DynamicSlugServer::class
        )->where('slug', '.*');

        Route::post(
            '/{slug}',
            DynamicSlugServer::class
        )->where('slug', '.*');
    }


    public static function registerLoginRoute()
    {
        if (is_callable(static::$loginRouteRegistrar)) {
            return call_user_func(static::$loginRouteRegistrar);
        }

        Route::get('/login', function () {
            return redirect(config('frontend.url') . '/account/login');
        })->name('login');
    }
}
