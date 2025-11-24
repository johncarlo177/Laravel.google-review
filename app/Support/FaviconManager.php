<?php

namespace App\Support;

use App\Interfaces\FileManager;
use App\Listeners\OnConfigChanged;
use App\Models\Config;
use App\Models\File;
use GeoIp2\Record\Continent;
use Illuminate\Support\Facades\Log;
use Throwable;

class FaviconManager
{
    public static function boot()
    {
        $instance = new static;

        OnConfigChanged::listen([$instance, 'onConfigChanged']);
    }

    public static function url($fileName)
    {
        $path = 'assets/favicon/' . $fileName;

        $time = '1';

        try {
            $time = filemtime(
                public_path($path)
            );
        } catch (Throwable $th) {
        }

        $version = "?v=$time";

        return asset("$path$version");
    }

    private function _onConfigChanged($key)
    {
        if ($key === 'app.name') {
            $this->saveAppNameInWebmanifest();
        }

        if ($key === 'theme.primary_0') {
            $this->saveThemeColorInWebmanifest();
        }

        if ($key == 'frontend.browserconfig.tile_color') {
            $this->saveMicrosoftTileColor();
        }

        if (preg_match('/favicon/', $key)) {
            $id = Config::get($key);

            if ($id)
                $this->publishFavicon(
                    File::find(
                        Config::get($key)
                    ),
                    $key
                );
        }
    }

    private function publishFavicons()
    {
        $keys = Config::select('key')
            ->where('key', 'like', '%favicon%')
            ->get()
            ->map(function ($item) {
                return $item->key;
            });

        foreach ($keys as $key) {
            Log::debug(sprintf('Publishing favicon for key %s', $key));

            $id = Config::get($key);

            $file = File::find($id);

            if (!$file) continue;

            $this->publishFavicon(
                $file,
                $key
            );
        }
    }

    public static function publish()
    {
        $instance = new static;

        $instance->saveAppNameInWebmanifest();

        $instance->saveThemeColorInWebmanifest();

        $instance->saveMicrosoftTileColor();

        $instance->publishFavicons();
    }

    public function onConfigChanged($key)
    {
        try {
            $this->_onConfigChanged($key);
        } catch (Throwable $th) {
            Log::error('Cannot sync favicons. ' . $th->getMessage());
        }
    }

    public function publishFavicon(File $file, string $configKeyName)
    {
        /**
         * @var FileManager
         */
        $files = app(FileManager::class);

        $content = $files->raw($file);

        $path = public_path(
            'assets/favicon/' .
                $this->getFaviconFileName($configKeyName)
        );

        file_put_contents($path, $content);
    }

    public function saveAppNameInWebmanifest()
    {
        $name = Config::get('app.name') ?? config('app.name');

        $this->saveWebmanifestField('name', $name);

        $this->saveWebmanifestField('short_name', $name);
    }

    private function saveMicrosoftTileColor($key = 'frontend.browserconfig.tile_color')
    {
        $path = public_path('assets/favicon/browserconfig.xml');

        $browserconfig = file_get_contents(
            $path
        );

        $browserconfig = preg_replace(
            '/<TileColor>.*<\/TileColor>/',
            sprintf(
                '<TileColor>%s</TileColor>',
                Config::get($key)
            ),
            $browserconfig
        );

        file_put_contents(
            $path,
            $browserconfig
        );
    }

    private function saveWebmanifestField($key, $value)
    {
        $path = 'assets/favicon/site.webmanifest';

        $webmanifest = json_decode(
            file_get_contents(
                public_path($path)
            ),
            true
        );

        $webmanifest[$key] = $value;

        file_put_contents(
            public_path($path),
            json_encode($webmanifest, JSON_PRETTY_PRINT)
        );
    }

    private function saveThemeColorInWebmanifest()
    {
        $primaryColor = Config::get('theme.primary_0');

        $this->saveWebmanifestField('theme_color', $primaryColor);
    }

    private function getFaviconFileName($configKey)
    {
        return str_replace('frontend.favicon-', '', $configKey);
    }
}
