<?php

namespace App\Support\SoftwareUpdate\AutoUpdate;

use App\Support\System\Traits\WriteLogs;
use Exception;
use Throwable;
use ZipArchive;

class UpdateRunner
{
    use WriteLogs;

    private $downloadLink = '';

    private $archive = 'script.zip';

    private $errors = [];

    private $isSilent = false;

    public static function withDownloadLink($link)
    {
        $instance = new static;

        $instance->downloadLink = $link;

        if (!str_starts_with($instance->downloadLink, 'https://quickcode.digital/download')) {
            throw new Exception('Invalid download link');
        }

        return $instance;
    }

    public function silently()
    {
        $this->isSilent = true;

        return $this;
    }

    public function run()
    {
        try {
            if ($this->isLocked()) {
                return $this->line('Update is locked, aborting.');
            }

            $this->lock();

            $this->init();

            $this->line("Downloading remote file");

            $this->download($this->downloadLink, $this->base_path($this->archive));

            $this->line("Backing up local confiugration.");

            $this->backupOriginalEnvFileIfNeeded();

            copy($this->base_path('.env'), $this->base_path('.env.current'));

            copy($this->base_path('.htaccess'), $this->base_path('.htaccess.current'));

            copy($this->base_path('public/.htaccess'), $this->base_path('public/.htaccess.current'));

            $this->line("Extracting main archive.");

            $this->unzip($this->base_path($this->archive), $this->base_path());

            // Delete all zipped files except public_html.zip

            $filesToDelete  = [
                ...glob($this->base_path('*.txt')),
                ...glob($this->base_path('*.zip')),
            ];

            foreach ($filesToDelete as $file) {
                if (preg_match('/public_html.zip$/', $file)) continue;

                unlink($file);
            }

            $this->line("Extracting public_html.zip");

            $this->unzip($this->base_path('public_html.zip'), $this->base_path());

            $this->line("Cleaning up public_html.zip");

            unlink($this->base_path('public_html.zip'));

            $this->line("Restoring configuration files.");

            copy($this->base_path('.env.current'), $this->base_path('.env'));

            copy($this->base_path('.htaccess.current'), $this->base_path('.htaccess'));

            copy($this->base_path('public/.htaccess.current'), $this->base_path('public/.htaccess'));

            // Clearing views cache.

            $this->line("Clearing views cache.");

            foreach (glob($this->base_path('storage/framework/views/*.php')) as $file) {
                unlink($file);
            }

            $this->clearLaravelCache();

            $this->clearConfigCache();

            $this->unlock();
        } catch (Throwable $th) {
            // 
            $this->logWarning(
                'Auto update failed with message: %s',
                $th->getMessage()
            );

            $this->errors[] = $th->getMessage();
        }

        if (empty($this->errors)) {
            $this->line("Update completed successfully ...");
        } else {
            $this->line("Update completed with errors. " . json_encode($this->errors, JSON_PRETTY_PRINT));
        }

        return $this;
    }

    public function didSucceed()
    {
        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function backupOriginalEnvFileIfNeeded()
    {
        if (file_exists($this->base_path('.env.original'))) {
            $this->line('.env.original is found.');
            return;
        }

        copy($this->base_path('.env'), $this->base_path('.env.original'));

        $this->line('Copying .env to .env.original');
    }

    private function lock()
    {
        file_put_contents($this->base_path('update.lock'), '');
    }

    private function unlock()
    {
        unlink($this->base_path('update.lock'));
    }

    private function isLocked()
    {
        return file_exists($this->base_path('update.lock'));
    }

    private function line($text)
    {
        if ($this->isSilent) {
            $this->logInfo($text);
            return;
        }

        echo "\n\r<br>\n\r";
        echo ">>> $text      <br>\n\r";

        try {
            ob_flush();
            flush();
        } catch (Throwable $th) {
            // 
        }
    }

    private function init()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('memory_limit', '200M');
        error_reporting(E_ALL);
        set_time_limit(0);
        ignore_user_abort(true);

        if (!$this->isSilent) {
            if (ob_get_level() == 0) {
                ob_start();
            }
        }

        if (!class_exists('ZipArchive')) {
            $this->line('ZipArchive not enabled.');
        }
    }

    /**
     * @return string
     */
    private function base_path($path = '')
    {
        return base_path($path);
    }

    private function unzip($src, $dist)
    {
        $zip = new ZipArchive;
        $res = $zip->open($src);

        if ($res === TRUE) {
            $zip->extractTo($dist);
            $zip->close();
            return true;
        }

        return false;
    }

    private function download($url, $path)
    {
        //This is the file where we save the    information
        $fp = fopen($path, 'w+');
        //Here is the file we are downloading, replace spaces with %20
        $ch = curl_init($url);
        // make sure to set timeout to a high enough value
        // if this is too low the download will be interrupted
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        // write curl response to file
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // get curl response
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    private function deleteDirectory($path)
    {
        if (is_dir($path)) {
            foreach (scandir($path) as $entry) {
                if (!in_array($entry, ['.', '..'], true)) {
                    $this->deleteDirectory($path . DIRECTORY_SEPARATOR . $entry);
                }
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    private function clearLaravelCache()
    {
        $cachePath = $this->base_path('storage/framework/cache/data');

        $this->line('Clearing laravel cache');

        $this->deleteDirectory($cachePath);

        mkdir($cachePath);
    }

    private function clearConfigCache()
    {
        $this->line('Clearing config cache');

        @unlink($this->base_path('bootstrap/cache/config.php'));
    }
}
