<?php

namespace App\Support\System;

use App\Http\Controllers\HomePageController;
use App\Models\Config;
use Illuminate\Support\Facades\Artisan;
use ReflectionClass;

class CacheManager
{
    private const TYPE_VIEW = 'view';

    private const TYPE_CONFIG = 'config';

    private $type = '';

    private function __construct($type)
    {
        if (!$this::validateType($type)) {
            $type = $this::TYPE_VIEW;
        }

        $this->type = $type;
    }

    public static function validateType($type)
    {
        $class = new ReflectionClass(static::class);

        $constants = $class->getConstants();

        return collect(array_values($constants))
            ->filter(fn($t) => $t === $type)->isNotEmpty();
    }

    public static function for($type)
    {
        return new static($type);
    }

    public function clear()
    {
        $this->clearDatabaseConfigCacheIfNeeded();

        $this->clearLaravelCacheIfNeeded();

        $this->clearOPCacheIfNeeded();

        $this->rebuildHomePageCacheIfNeeded();

        Artisan::call(sprintf('%s:clear', $this->type));
    }

    protected function rebuildHomePageCacheIfNeeded()
    {
        if ($this->type != static::TYPE_VIEW) {
            return;
        }

        HomePageController::rebuildHomePageCache();
    }

    protected function clearOPCacheIfNeeded()
    {
        if ($this->type != static::TYPE_VIEW) {
            return;
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    public function rebuild()
    {
        $this->clear();

        $this->rebuildDatabaseConfigCacheIfNeeded();

        Artisan::call(sprintf('%s:cache', $this->type));
    }

    private function isConfig()
    {
        return $this->type === $this::TYPE_CONFIG;
    }

    private function clearLaravelCacheIfNeeded()
    {
        if (!$this->isConfig()) {
            return;
        }

        Artisan::call('cache:clear');
    }

    private function clearDatabaseConfigCacheIfNeeded()
    {
        if (!$this->isConfig()) return;

        Config::clearCache();
    }

    private function rebuildDatabaseConfigCacheIfNeeded()
    {
        if (!$this->isConfig()) return;

        Config::rebuildCache();
    }
}
