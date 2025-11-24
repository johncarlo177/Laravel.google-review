<?php

namespace App\Support\SoftwareUpdate\AutoUpdate;

use App\Support\System\Traits\HasClassSettings;
use Illuminate\Support\Facades\Http;

class DownloadLinkGenerator
{
    use HasClassSettings;

    public function generate()
    {
        if (!$this->shouldGenerateLink()) {
            return null;
        }

        return $this->generateLink();
    }

    protected function shouldGenerateLink()
    {
        $version = new SoftwareVersion;

        return $version->hasUpdate();
    }

    protected function generateLink()
    {
        $response = Http::post(
            'https://quickcode.digital/api/generate-download-link',
            [
                'license-key' => config('app.purchase_code')
            ]
        );

        $url = $response->json('url');

        return $url;
    }
}
