<?php

namespace App\Support;

use App\Support\System\CacheManager;

class ConfigFileManager
{
    private static function afterSave()
    {
        CacheManager::for('config')->rebuild();
    }

    public static function save($name, $value)
    {
        $file = explode('.', $name)[0];
        $key = @explode('.', $name)[1];

        $path = base_path('config/' . $file . '.php');

        $config = config($file);

        if (!is_array($config)) {
            $config = [];
        }

        if (!empty($key)) {
            $config[$key] = $value;
            config([$name => $value]);
        } else
            $config = $value;

        $exports = var_export($config, true);

        file_put_contents($path, "<?php return $exports;");

        static::afterSave();
    }

    public static function saveJson($name, $value)
    {
        return static::save($name, json_encode($value));
    }
}
