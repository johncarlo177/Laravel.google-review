<?php

namespace App\Http\Controllers;

use App\Plugins\BasePlugin;
use App\Plugins\PluginManager;
use App\Plugins\PluginResponse;

class PluginsController extends Controller
{
    private PluginManager $plugins;

    public function __construct()
    {
        $this->plugins = new PluginManager();
    }

    public function listInstalledPlugins()
    {
        return $this->plugins
            ->getEnabledPlugins()
            ->filter(function (BasePlugin $plugin) {
                return $plugin->shouldShowInPluginsUi();
            })
            ->map(function (BasePlugin $plugin) {
                return PluginResponse::forPlugin($plugin)
                    ->includeConfigs()
                    ->toArray();
            })->values();
    }

    public function viewPlugin($slug)
    {
        $plugin = $this->plugins->find($slug);

        if (!$plugin) {
            abort(404);
        }

        return PluginResponse::forPlugin($plugin)
            ->includeConfigs()
            ->toArray();
    }
}
