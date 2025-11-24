<?php

namespace App\Support\Sms\Drivers;

use App\Models\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseDriver
{
    public abstract function slug();

    protected abstract function doSend(string $to, string $text);

    public function send(string $to, string $text)
    {
        try {
            return $this->doSend($to, $text);
        } catch (Throwable $th) {
            Log::error(class_basename(static::class) . ' error sending sms ' . $th->getMessage());
        }
    }

    public function enable()
    {
        return $this->config(['enabled' => true]);
    }

    public function disable()
    {
        return $this->config(['enabled' => false]);
    }

    public function isEnabled()
    {
        return $this->config('enabled');
    }

    public function config($key)
    {
        if (is_array($key)) {
            $_key = array_keys($key)[0];
            $_value = $key[$_key];

            return $this->setConfig($_key, $_value);
        }

        return $this->getConfig($key);
    }

    protected function getConfig($key)
    {
        return Config::get($this->configKey($key));
    }

    protected function setConfig($key, $value)
    {
        return Config::set($this->configKey($key), $value);
    }

    protected function configKey($key)
    {
        return sprintf('sms-gateways.%s.%s', $this->slug(), $key);
    }
}
