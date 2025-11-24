<?php

namespace App\Plugins;


class PluginResponse
{
    private BasePlugin $plugin;

    private bool $includeConfigs = false;

    public static function forPlugin(BasePlugin $plugin)
    {
        $instance = new static;

        $instance->plugin  = $plugin;

        return $instance;
    }

    public function includeConfigs()
    {
        $this->includeConfigs = true;

        return $this;
    }

    public function toArray()
    {
        $result = [
            'name' => $this->plugin->name(),
            'description' => $this->plugin->description(),
            'tags' => $this->plugin->tags(),
            'slug' => $this->plugin->slug(),
            'show_settings_link' => $this->plugin->shouldShowSettingsLink()
        ];

        if ($this->includeConfigs) {
            $result['configs'] = $this->plugin->serveConfig();
        }

        return $result;
    }
}
