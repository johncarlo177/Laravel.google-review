<?php

namespace App\Models;

use Exception;
use App\Events\ConfigChanged;
use App\Events\ConfigWillChange;
use App\Interfaces\FileManager;
use App\Models\Traits\HasMetaFields;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * @property string key
 * @property string value
 */
class Config extends Model
{
    /**
     * Line translation is based on Config meta fields.
     */
    use HasMetaFields;

    use WriteLogs;

    use HasFactory;

    private static $configsArray = null;

    private static $useCache = true;

    public static function enableCache()
    {
        static::$useCache = true;
    }

    public static function disableCache()
    {
        static::$useCache = false;
    }

    public static function boot()
    {
        parent::boot();
    }

    public static function getId($key)
    {
        return static::where('key', $key)->first()?->id;
    }

    /**
     * Bypass cached array.
     */
    public static function getReal($key)
    {
        static::disableCache();

        $value = static::get($key);

        static::enableCache();

        return $value;
    }

    public static function get($key)
    {
        if (!config('app.installed')) {
            return;
        }

        try {

            if (static::$useCache) {
                return @static::asArray()[$key]['value'];
            }

            $value = static::fetch($key);

            return $value;
            // 
        } catch (Throwable $th) {
            return null;
        }
    }

    public static function fetch($key)
    {
        $item = static::where('key', $key)->first();

        $value = static::parseValue($item?->value);

        return $value;
    }

    public static function set($key, $value)
    {
        $record = static::where('key', $key)->first();

        if (!$record) {
            $record = new static;
            $record->key = $key;
        }

        ConfigWillChange::fire($key, $value);

        if ($value)
            $record->value = json_encode($value);
        else
            $record->value = $value;

        $record->save();

        ConfigChanged::fire($key);

        return $record;
    }

    public static function fileUrl($key)
    {
        /**
         * @var FileManager
         */
        $files = app(FileManager::class);

        if ($fileId = static::get($key)) {

            $file = File::find($fileId);

            if ($file) {
                return $files->url($file);
            }
        }

        return null;
    }

    private static function fetchConfigs()
    {
        try {
            return static::all()->reduce(
                function ($result, $item) {

                    $result[$item->key]['id'] = $item->id;
                    $result[$item->key]['value'] = static::parseValue($item->value);

                    return $result;
                },
                []
            );
        } catch (Throwable $th) {
            return collect([]);
        }
    }


    private static function cachedConfigsArray()
    {
        return Cache::remember(
            static::cacheKey(),
            3600 * 72,
            function () {
                return static::fetchConfigs();
            }
        );
    }

    private static function cacheKey()
    {
        return static::class . '::configsTotalArray';
    }

    public static function clearCache()
    {
        Cache::delete(static::cacheKey());

        static::$configsArray = null;
    }

    public static function rebuildCache()
    {
        static::clearCache();

        static::asArray();
    }

    public static function syncMemoryConfigs()
    {
        static::$configsArray = static::fetchConfigs();
    }

    public static function asArray()
    {
        if (!static::$configsArray) {
            static::$configsArray = static::cachedConfigsArray();
        }

        return static::$configsArray;
    }

    private static function parseValue($value)
    {
        try {

            if (is_string($value) && json_validate($value)) {
                $value = json_decode($value, true);
            }

            if (is_string($value))
                switch ($value) {
                    case 'NULL':
                    case 'null':
                        return null;

                    case 'true':
                    case 'TRUE':
                        return true;

                    case 'false':
                    case 'FALSE':
                        return false;
                }

            return $value;
        } catch (Exception $ex) {
            static::logError($ex->getMessage());
        }

        return $value;
    }
}
