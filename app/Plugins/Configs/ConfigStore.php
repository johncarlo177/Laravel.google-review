<?php

namespace App\Plugins\Configs;

use App\Models\Config;
use App\Plugins\BasePlugin;
use Throwable;

class ConfigStore
{
    private BasePlugin $plugin;

    public function __construct(BasePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function serve()
    {
        $this->populateDefaultValuesIfNeeded();

        return collect(
            $this->plugin->configDefs()
        )->map(function (ConfigSection $section) {

            return $this->serveConfigSection($section);
        });
    }

    private function allConfigDefs()
    {
        return collect($this->plugin->configDefs())
            ->reduce(function ($result, ConfigSection $section) {

                return array_merge(
                    $result,
                    $section->fields
                );
            }, []);
    }

    private function resolveItem($key): ConfigDef
    {
        return collect($this->allConfigDefs())->first(
            function (ConfigDef $item) use ($key) {
                return $item->key === $key;
            }
        );
    }

    public function get($key)
    {
        return Config::get($this->key($key));
    }

    public function set($key, $value)
    {
        $result = Config::set(
            $this->key($key),
            $value
        );

        Config::rebuildCache();

        return $result;
    }

    public function populateDefaultValuesIfNeeded()
    {

        try {
            if ($this->didPopulateDefaultValues()) return;

            collect($this->allConfigDefs())->each(function (ConfigDef $item) {
                $this->set($item->key, $item->defaultValue);
            });

            $this->setPopulatedDefaultValues();
        } catch (Throwable $th) {
            //
        }
    }

    private function didPopulateDefaultValues()
    {
        return Config::get($this->key('__populate_default_values__'));
    }

    private function setPopulatedDefaultValues()
    {
        Config::set($this->key('__populate_default_values__'), true);
    }

    private function serveConfigSection(ConfigSection $section)
    {
        $section->fields = array_map(function (ConfigDef $item) {
            return $this->serveConfigItem($item);
        }, $section->fields);

        return $section;
    }

    private function serveConfigItem(ConfigDef $item)
    {
        $value = $this->get($item->key);

        $item->value = $value;

        $item->expandedKey = $this->key($item->key);

        return $item->toArray();
    }

    protected function key($key)
    {
        return sprintf('%s.%s.%s', 'plugin', $this->plugin->slug(), $key);
    }
}
