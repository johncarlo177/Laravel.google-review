<?php

namespace App\Repositories;

use App\Interfaces\EnvSaver as EnvSaverInterface;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Cache;

class EnvSaver implements EnvSaverInterface
{
    public $env = '.env';

    private $cacheKey = 'EnvSaver::env';

    private function env()
    {
        return base_path($this->env);
    }

    private function filterSave(string $key, $value)
    {
        if (empty($value) && !is_numeric($value)) {
            return 'null';
        }

        if (is_string($value) && preg_match('/[ #\'"\(\)\?+\-*\/&^%$!@]/', $value)) {
            // Escape double qoutes and backslash
            $value = str_replace('"', '\"', $value);

            $value = str_replace('\\', str_repeat('\\', 4), $value);

            $value = '"' . $value . '"';
        }

        return $value;
    }

    public function save(string $key, ?string $value)
    {
        $content = trim(file_get_contents($this->env()));

        Cache::put($this->cacheKey, $content);

        $value = $this->filterSave($key, $value);

        if (!preg_match("/$key/", $content)) {
            // Key was not found, append it to the .env file
            $result = $content . "\r\n\r\n# Added based on user input.\r\n$key=$value\n\r";
        } else {
            $result = preg_replace("/$key=.*/", "$key=$value", $content);
        }

        file_put_contents($this->env(), $result);
    }

    public function saveMany(array $variables)
    {
        foreach ($variables as $key => $value) {
            $this->save($key, $value);
        }
    }

    public function load(array $keys)
    {
        $env = Dotenv::createUnsafeMutable(base_path())->load();

        $filtered = array_filter(
            $env,
            fn ($val, $key) => array_search($key, $keys) !== false,
            ARRAY_FILTER_USE_BOTH
        );

        return $filtered;
    }

    public function rollback()
    {
        $content = Cache::get($this->cacheKey);

        if (empty($content)) {
            return;
        }

        file_put_contents($this->env(), $content);
    }
}
