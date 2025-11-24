<?php

namespace App\Support\SoftwareUpdate\AutoUpdate;

use App\Plugins\PluginManager;
use App\Support\System\Traits\HasClassSettings;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class SoftwareVersion
{
    use WriteLogs, HasClassSettings;

    public function getLatestVersion()
    {
        $version = $this->getLatestVersionRawValue();

        return str_replace('v', '', $version);
    }

    protected function getLatestVersionRawValue()
    {
        if (!$this->shouldFetchLatestVersion()) {
            return $this->getConfig('latest_version');
        }

        return $this->fetchLatestVersion();
    }

    protected function shouldFetchLatestVersion()
    {
        if (empty($this->getConfig('latest_version'))) {
            return true;
        }

        // Fetch every day once.
        return $this->getFetchDate()->diffInDays(now(), true) > 1;
    }

    protected function getFetchDate()
    {
        $timestamp = $this->getConfig('fetch_timestamp');

        return new Carbon($timestamp);
    }

    protected function fetchLatestVersion()
    {
        $this->logDebug('Fetching latest version');

        try {
            $version = Http::get(
                'https://quickcode.digital/api/current-version'
            )->json(
                'version'
            );
        } catch (Throwable $th) {
            $this->logWarning($th->getMessage());

            $version = $this->getCurrentVersion();
        }


        $this->setConfig('latest_version', $version);

        $this->setConfig('fetch_timestamp', now()->timestamp);

        return $version;
    }

    public function hasUpdate()
    {
        $updateIsAvailable = $this->versionNumber($this->getLatestVersion()) > $this->versionNumber($this->getCurrentVersion());

        $updateIsAvailable = PluginManager::doFilter(
            PluginManager::FILTER_UPDATE_IS_AVAILABLE,
            $updateIsAvailable
        );

        return $updateIsAvailable;
    }

    protected function versionNumber($version)
    {
        $version = str_replace('v', '', $version);

        $version = str_replace('.', '', $version);

        return $version;
    }

    public function getCurrentVersion()
    {
        return json_decode(
            file_get_contents(
                base_path('composer.json')
            )
        )->version;
    }
}
