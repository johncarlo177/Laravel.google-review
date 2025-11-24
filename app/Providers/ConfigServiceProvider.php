<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Config as ConfigModel;
use App\Interfaces\FileManager;
use App\Models\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('app.installed')) return;

        try {
            $this->bindDatabaseConfigurations();

            $this->bindAppName();

            $this->bindTimezone();
        } catch (\Throwable $th) {
            /**
             * Boot would fail on docker container build.
             * When executing the command @php artisan package:discover --ansi
             * The database connection won't be ready then.
             */
        }


        $this->bindFileUrls([
            'frontend.header_logo',
            'frontend.header_logo_inverse',
            'frontend.login_logo',
            'account_page.background_image',
        ]);
    }

    private function bindFileUrls($keys)
    {
        foreach ($keys as $key) {
            $this->bindFileUrlConfig($key);
        }
    }

    private function bindFileUrlConfig($configKeyOfFileId)
    {
        try {
            $files = app(FileManager::class);

            if ($fileId = Config::get($configKeyOfFileId)) {

                $file = File::find($fileId);

                if ($file) {
                    Config::set(
                        $configKeyOfFileId . '_url',
                        $files->url($file)
                    );
                }
            }
        } catch (Throwable $th) {
            Log::error('Error while in ConfigServiceProvider::bindFileUrlConfig ' . $th->getMessage(), compact('configKeyOfFileId'));
        }
    }

    private function bindDatabaseConfigurations()
    {
        foreach (ConfigModel::asArray() as $key => $value) {
            Config::set($key, $value['value']);
        }
    }

    private function bindAppName()
    {
        $name = ConfigModel::get('app.name');

        if (!empty($name)) {
            Config::set('app.name', $name);
        }
    }

    private function bindTimezone()
    {
        $timezone = ConfigModel::get("app.timezone");

        if (!empty($timezone)) {
            Config::set('app.timezone', $timezone);
        }
    }
}
