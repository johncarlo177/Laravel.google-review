<?php

namespace App\Plugins;

use App\Console\Kernel;
use App\Models\User;
use App\Plugins\Configs\ConfigStore;
use App\Plugins\Configs\ConfigSection;
use App\Plugins\Configs\UserConfigStore;
use App\Providers\PluginsServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Support\MimeTypeResolver;
use App\Support\System\Interfaces\HasLoggerPrefix;
use App\Support\System\MemoryCache;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\View\FileViewFinder;
use Illuminate\Console\Application as ArtisanApplication;
use Throwable;

abstract class BasePlugin implements HasLoggerPrefix
{
    use WriteLogs;

    public ConfigStore $config;

    protected FileViewFinder $viewFinder;

    protected PluginsServiceProvider $pluginsProvider;

    public function __construct()
    {
        $this->config = $this->globalConfig();

        $this->viewFinder = app('view.finder');

        $this->pluginsProvider = app('plugins.provider');
    }

    public static function getLoggerPrefix(): string
    {
        return (new static)->getShortNamespace();
    }


    public static function config(): ConfigStore
    {
        return MemoryCache::remember(static::class . __METHOD__, function () {
            return (new static)->globalConfig();
        });
    }

    public final function register()
    {
        $this->registerPlugin();

        $this->extendHeadConfigs();

        Kernel::addSchedule([$this, 'schedule']);

        RouteServiceProvider::registerApiRoutes([$this, 'registerApiRoutes']);

        RouteServiceProvider::registerWebRoutes([$this, 'registerWebRoutes']);

        RouteServiceProvider::regsiterWebhooksRoutes(
            $this->registerWebhooksRoutes(...)
        );

        $this->registerViewPath();

        $this->registerMigrationsFolder();

        $this->registerTranslationFolder();

        $this->bindSingleton();
    }

    public final function boot()
    {
        $this->bootPlugin();
    }

    public function name()
    {
        $kebab = Str::kebab(
            class_basename($this->getNamespace())
        );

        $space = str_replace('-', ' ', $kebab);

        return Str::title($space);
    }

    protected function extendHeadConfigs()
    {
        PluginManager::addFilter(
            PluginManager::FILTER_HEAD_CONFIG_ARRAY,
            fn($config) => array_merge($config, $this->getHeadConfigs())
        );
    }

    protected function getHeadConfigs()
    {
        return [];
    }

    public function description()
    {
        return '';
    }

    public function tags()
    {
        return [];
    }

    public function shouldShowInPluginsUi()
    {
        return false;
    }

    public function slug()
    {
        return $this->guessSlug();
    }

    /**
     * @return ConfigSection[]
     */
    public function configDefs()
    {
        return [];
    }

    public function serveConfig()
    {
        return $this->config()->serve();
    }

    protected function registerPlugin()
    {
        // 
    }

    protected function bootPlugin()
    {
        // to be implemented whenever needed in child plugin
    }

    public final function schedule(Schedule $schedule)
    {
        $this->pluginSchedule($schedule);
    }

    public function isEnabled()
    {
        return false;
    }

    protected function pluginSchedule(Schedule $schedule) {}

    protected function registerCommand($commandClass)
    {
        ArtisanApplication::starting(function ($artisan) use ($commandClass) {
            $artisan->resolve($commandClass);
        });
    }

    private function registerViewPath()
    {
        $paths = $this->viewFinder->getPaths();

        $path = base_path('app/Plugins/' . basename($this->pluginDir()) . '/views');

        if (!file_exists($path)) return;

        $paths[] = $path;

        $this->viewFinder->setPaths($paths);
    }

    public function registerApiRoutes()
    {
        Route::prefix($this->getRoutePrefix())->group(function () {
            $this->apiRoutes();

            $this->requireRoutesFile('api');
        });
    }

    public function registerWebRoutes()
    {
        Route::prefix($this->getRoutePrefix())->group(function () {
            $this->webRoutes();

            $this->requireRoutesFile('web');

            $this->registerPublicRoutes();
        });
    }

    public function registerWebhooksRoutes()
    {
        Route::prefix($this->getRoutePrefix())->group(function () {
            $this->requireRoutesFile('webhooks');
        });
    }

    private function registerPublicRoutes()
    {
        $this->registerCorsPath();

        Route::get('{path}', function (Request $request) {
            return $this->servePublicRoute($request);
        })->where('path', '.*');
    }

    private function registerCorsPath()
    {
        config(['cors.paths' => [
            ...config('cors.paths'),
            $this->getRoutePrefix() . '/*'
        ]]);
    }

    public function asset($path)
    {
        $path = sprintf('%s/%s', $this->getRoutePrefix(), $path);

        $path = str_replace('//', '/', $path);

        return url($path);
    }

    private function servePublicRoute(Request $request)
    {
        $path = $request->route('path');

        $file = $this->pluginDir() . '/public/' . $path;

        if (!file_exists($file)) {
            $this->logDebugf('file %s not found in %s', $path, $file);

            abort(404);
        }

        return response(file_get_contents($file), 200, [
            'Content-Type' => MimeTypeResolver::resolve($file)
        ], [
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'max-age=31536000'
        ]);
    }

    private function pluginDir()
    {
        $classInfo = new ReflectionClass($this);

        return dirname($classInfo->getFileName());
    }

    public function path($file)
    {
        $path = sprintf('%s/%s', $this->pluginDir(), $file);

        $path = str_replace('//', '/', $path);

        return $path;
    }

    protected function apiRoutes() {}

    protected function webRoutes() {}

    protected function getNamespace()
    {
        $reflection_class = new \ReflectionClass(static::class);

        $namespace = $reflection_class->getNamespaceName();

        return $namespace;
    }

    protected function getRoutePrefix()
    {
        return $this->slug();
    }

    protected function getShortNamespace()
    {
        $namespace = $this->getNamespace();

        $namespace = class_basename($namespace);

        return $namespace;
    }

    protected function guessSlug()
    {
        return strtolower($this->getShortNamespace());
    }

    /**
     * Use guessSlug instead
     * @deprecated
     */
    protected function routesPrefix()
    {
        return $this->guessSlug();
    }

    protected function registerMigrationsFolder()
    {
        $path = $this->pluginDir() . '/migrations';

        if (is_dir($path)) {
            PluginsServiceProvider::registerMigrationsFolder($path);
        }
    }

    private function requireRoutesFile($type)
    {
        $path = sprintf('%s/routes/%s.php', $this->pluginDir(), $type);

        if (file_exists($path)) {
            require_once $path;
        }
    }

    /**
     * Helper function to determine whether a plugin is running on 
     * a specified domain or no
     */
    protected function hostedOn($hostName)
    {
        $host = request()->host();

        return preg_match("/$hostName/i", $host);
    }

    protected function localOrHostedOn($hostName)
    {
        return app()->environment('local') || $this->hostedOn($hostName) || $this->hostedOn('myip');
    }

    private function hasTranslationFolder()
    {
        $files = glob(
            sprintf('%s/*.json', $this->translationFolderPath())
        );

        return !empty($files);
    }

    private function translationFolderPath()
    {
        return $this->path('lang');
    }

    private function registerTranslationFolder()
    {
        if (!$this->hasTranslationFolder()) return;

        $this->pluginsProvider->registerLanguageJsonFile(
            $this->translationFolderPath()
        );
    }

    private function bindSingleton()
    {
        app()->singleton(
            static::class,
            fn() => $this
        );
    }

    public function globalConfig()
    {
        return new ConfigStore($this);
    }

    public function userConfig(?User $user = null)
    {
        $user = $user ?: request()->user();

        return (new UserConfigStore($this))->withUser($user);
    }

    public function shouldShowSettingsLink()
    {
        return collect($this->configDefs())->isNotEmpty();
    }


    protected function linkPublicFolder($linkName = null)
    {
        $linkName = $linkName ?? $this->slug();

        $link = base_path('public/') . $linkName;

        if (file_exists($link)) {
            return;
        }

        $source = base_path(
            sprintf('app/Plugins/%s/public', $this->getShortNamespace())
        );

        $this->logError('Linking %s to %s', $source, $link);

        try {
            symlink($source, $link);
        } catch (Throwable $th) {
            $this->logError($th->getMessage());
        }
    }
}
