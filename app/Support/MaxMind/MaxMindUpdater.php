<?php

namespace App\Support\MaxMind;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PharData;

class MaxMindUpdater
{

    private function fs(): ?FilesystemAdapter
    {
        $disk = Storage::disk('local');

        if (!($disk instanceof FilesystemAdapter)) {
            return null;
        }

        return $disk;
    }

    private function baseDirectory()
    {
        return 'maxmind';
    }

    private function tmpDownloadedFilePath($absolute, $tar = false)
    {
        $path = $this->path('db.tar.gz', $absolute);

        if ($tar)
            return str_replace('.gz', '', $path);

        return $path;
    }

    private function tmpDbFolder($absolute)
    {
        return $this->path('db', $absolute);
    }

    public function databaseFileName($absolute)
    {
        return $this->path('db.mmdb', $absolute);
    }

    /**
     * @return string storage path to given file, or full path if $absolute is true
     */
    private function path($fileName, $absolute = false)
    {
        $path = $this->baseDirectory() . '/' . $fileName;

        if ($absolute) {
            return $this->fs()->path($path);
        }

        return $path;
    }

    private function url()
    {
        return sprintf(
            'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz',
            config('services.maxmind.license_key')
        );
    }

    public function update()
    {
        $this->download();

        $this->extract();

        $this->moveExtractedDatabaseFile();

        $this->clearTmpFiles();
    }

    private function clearTmpFiles()
    {
        $this->fs()->deleteDirectory($this->tmpDbFolder(false));

        $this->fs()->delete($this->tmpDownloadedFilePath(false));
        $this->fs()->delete($this->tmpDownloadedFilePath(false, true));
    }

    private function moveExtractedDatabaseFile()
    {
        rename(
            $this->getTmpDownloadedDatabaseFileName(),
            $this->databaseFileName(true)
        );
    }

    private function getTmpDownloadedDatabaseFileName()
    {
        $pattern = $this->tmpDbFolder(true) . '/**/*.mmdb';

        $files = glob($pattern);

        return $files[0];
    }

    private function download()
    {
        $contents = Http::timeout(0)->get($this->url())->body();

        $this->fs()
            ->put(
                $this->tmpDownloadedFilePath(absolute: false),
                $contents
            );
    }

    private function extract()
    {
        // decompress from gz
        $p = new PharData($this->tmpDownloadedFilePath(true));
        $p->decompress(); // creates /path/to/my.tar

        // unarchive from the tar
        $phar = new PharData($this->tmpDownloadedFilePath(true, true));

        $phar->extractTo($this->tmpDbFolder(true));
    }
}
