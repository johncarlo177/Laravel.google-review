<?php

namespace App\Providers;

use App\Plugins\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginsServiceProvider extends ServiceProvider
{
    private ?PluginManager $_manager = null;

    private static $migrationFolders = [];

    private function manager()
    {
        if (!$this->_manager) {
            $this->_manager = new PluginManager();
        }

        return $this->_manager;
    }

    public static function registerMigrationsFolder(string $path)
    {
        static::$migrationFolders[] = $path;
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('plugins.provider', fn () => $this);

        $this->manager()->register();

        $this->prepareMigrationFolders();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->manager()->boot();
    }

    private function prepareMigrationFolders()
    {
        $this::$migrationFolders[] = base_path('database/migrations');

        $this->loadMigrationsFrom($this::$migrationFolders);
    }

    public function registerLanguageJsonFile($path)
    {
        $this->loadJsonTranslationsFrom($path);
    }
}
