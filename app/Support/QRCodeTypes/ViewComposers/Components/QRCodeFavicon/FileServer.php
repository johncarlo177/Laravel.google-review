<?php

namespace App\Support\QRCodeTypes\ViewComposers\Components\QRCodeFavicon;

use App\Http\Middleware\CustomDomainServer;
use App\Interfaces\FileManager;
use App\Models\File;
use App\Models\QRCode;
use App\Models\QRCodeWebPageDesign;

class FileServer
{
    private QRCode $qrcode;

    private ?QRCodeWebPageDesign $design;

    private FileManager $files;

    const ROUTE = '/qrcodes/{qrcode}/favicon/{fileName}';

    public static function boot()
    {
        CustomDomainServer::excludePattern(
            '/qrcodes.\d+.favicon.*/'
        );
    }

    public function __construct(QRCode $qrcode)
    {
        $this->qrcode = $qrcode;

        $this->files = app(FileManager::class);

        $this->design = QRCodeWebPageDesign::where('qrcode_id', $this->qrcode->id)->first();
    }

    public function route($fileName)
    {
        $route = str_replace('{qrcode}', $this->qrcode->id, $this::ROUTE);

        $route = str_replace('{fileName}', $fileName, $route);

        return url($route);
    }

    public function serve($fileName)
    {
        $file = $this->findFile($fileName);

        if (!$file) return $this->serveStaticFile($fileName);

        return response(
            $this->files->raw($file),
            200,
            [
                'Content-Type' => $file->mime_type
            ]
        );
    }

    private function findFile($name): ?File
    {
        $fileId = $this->designValue($name);

        return File::find($fileId);
    }

    private function serveStaticFile($fileName)
    {
        switch ($fileName) {
            case 'site.webmanifest':
                return response(
                    $this->generateWebManifest(),
                    200,
                    [
                        'Content-Type' => 'application/json'
                    ]
                );
        }

        return null;
    }

    private function generateWebManifest()
    {
        return json_encode(
            [
                "name" => $this->qrcode->name,
                "short_name" => $this->qrcode->name,
                "icons" => [
                    [
                        "src" => $this->route('web-app-manifest-192x192.png'),
                        "sizes" => "192x192",
                        "type" => "image/png",
                        "purpose" => "maskable",
                    ],
                    [
                        "src" => $this->route('web-app-manifest-512x512.png'),
                        "sizes" => "512x512",
                        "type" => "image/png",
                        "purpose" => "maskable",
                    ]
                ],
                "theme_color" => "#ffffff",
                "background_color" => "#ffffff",
                "display" => "standalone",
                "start_url" => $this->qrcode->redirect->route,
                "orientation" => "portrait-primary",
            ]
        );
    }

    private function designValue($key)
    {
        $value = $this?->design?->value($key);

        if (empty($value)) return null;

        return $value;
    }
}
