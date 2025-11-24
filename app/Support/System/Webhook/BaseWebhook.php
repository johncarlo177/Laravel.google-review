<?php

namespace App\Support\System\Webhook;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use ZipArchive;

abstract class BaseWebhook
{
    use WriteLogs;

    public abstract function slug();

    protected abstract function handleWebhook(Request $request);

    public final function handle(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            $this->logError('QuickCode webhook is not verified. %s', $this->slug());
            return;
        } else {
            $this->logInfo('QuickCode webhook is verified successfully');
        }

        return $this->handleWebhook($request);
    }

    protected function verifyWebhook(Request $request)
    {
        $verified = Http::post(
            'https://quickcode.digital/api/verify-communication',
            $request->all()
        )->json('verified');

        return $verified;
    }

    protected function extractArchive($src, $dist)
    {
        $zip = new ZipArchive;

        $res = $zip->open($src);

        if ($res === TRUE) {

            $zip->extractTo($dist);

            $zip->close();

            return true;
        }

        $this->logError('Cannot open zip archive %s', $src);

        return false;
    }

    protected function downloadFile($fromUrl, $toPath)
    {
        set_time_limit(0);
        //This is the file where we save the    information
        $fp = fopen($toPath, 'w+');
        //Here is the file we are downloading, replace spaces with %20
        $ch = curl_init($fromUrl);
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

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status != 200) {
            unlink($toPath);
        }
    }
}
