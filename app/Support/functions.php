<?php

use App\Models\File;
use App\Repositories\FileManager;
use App\Repositories\TranslationManager;
use App\Support\System\MemoryCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

error_reporting(E_ALL ^ E_WARNING);

function isDemo()
{
    return app()->environment('demo');
}

function is_dev()
{
    $host = request()->host();

    return app()->environment('local') && preg_match("/quickcode.test/", $host);
}

function app_installed()
{
    return config('app.installed');
}


function isLocal()
{
    $host = request()->host();

    $local = preg_match("/localhost/", $host);

    return app()->environment('local') || $local;
}

function t($text)
{
    return TranslationManager::t($text);
}

function file_url($id)
{
    if (!config('app.installed')) return;

    if (is_array($id)) {
        return null;
    }

    return Cache::rememberForever(

        __METHOD__ . $id,

        function () use ($id) {

            $files = new FileManager();

            $file = File::find($id);

            if (!$file) return null;

            return $files->url($file);
        }
    );
}

function config_file_url($key)
{
    return \App\Models\Config::fileUrl($key);
}

function has_custom_frontend()
{
    return !empty(config('app.frontend_custom_url')) || (config('app.use_login_screen_as_home_page') === 'yes');
}

function frontend_custom_url($path = null)
{
    $url = config('app.frontend_custom_url');

    if (!empty($path)) {
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
    }

    return $url ? $url . $path : null;
}

function asset_with_version_query_param($relativePath)
{
    try {
        $v = filemtime(public_path($relativePath));

        return asset("$relativePath?v=$v");
    } catch (Throwable $th) {
        return asset($relativePath);
    }
}

function override_asset($url, $addVersionQueryParam = false)
{
    $path = explode('?', $url)[0];

    $overridePath = str_replace('//', '/', '/override/' . $path);

    $returnUrl = function ($url) use ($addVersionQueryParam) {
        if ($addVersionQueryParam) {
            return asset_with_version_query_param($url);
        }

        return asset($url);
    };

    if (file_exists(public_path($overridePath))) {
        return $returnUrl($overridePath);
    }

    return $returnUrl($url);
}


function override_asset_path($path)
{
    $overridePath = str_replace('//', '/', '/override/' . $path);

    if (file_exists(public_path($overridePath))) {
        return public_path($overridePath);
    }

    return public_path($path);
}


function log_database_queries()
{
    DB::listen(function ($query) {
        Log::debug(
            $query->sql,
            [
                'bindings' => $query->bindings,
                'time' => $query->time
            ]
        );
    });
}

function is_base64($string): bool
{
    if (!is_string($string)) {
        // if check value is not string.
        // base64_decode require this argument to be string, if not then just return `false`.
        // don't use type hint because `false` value will be converted to empty string.
        return false;
    }

    $decoded = base64_decode($string, true);
    if (false === $decoded) {
        return false;
    }

    if (json_encode([$decoded]) === false) {
        return false;
    }

    return true;
}

function showing_api_docs()
{
    return preg_match('#docs/api#', request()->path());
}

function mem($label)
{
    Log::debug($label . ' | usage: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB");

    Log::debug($label . ' | peak: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB");
}
