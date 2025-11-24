<?php

namespace App\Support\System\Traits;

use App\Models\Config;

trait HasClassSettings
{
    protected function key($key)
    {
        return sha1(static::class) . '::' . $key;
    }

    protected function getConfig($key)
    {
        $config = Config::fetch($this->key($key));

        return $config;
    }

    protected function setConfig($key, $value)
    {
        Config::set($this->key($key), $value);
    }
}
