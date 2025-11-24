<?php

namespace App\Support;

use App\Interfaces\FileManager;
use App\Models\QRCode;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class QRCodeStorage
{
    private QRCode $qrcode;

    private FileManager $files;

    public const SERVE_QRCODE_SVG_FILE_ROUTE = '/qrcodes/{qrcode}/serve_svg_file';

    private function __construct()
    {
        $this->files = app(FileManager::class);
    }

    public static function ofQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->setQRCode($qrcode);

        return $instance;
    }

    private function setQRCode(QRCode $qrcode)
    {
        $this->qrcode = $qrcode;
    }

    private function fs()
    {
        return $this->files->fs();
    }

    private function fsAdapter(): ?FilesystemAdapter
    {
        if ($this->fs() instanceof FilesystemAdapter) {
            return $this->fs();
        }

        return null;
    }

    public function store($svgContent)
    {
        $this->fs()->put(
            $this->getRelativeSvgPath(),
            $svgContent
        );
    }

    public function getSvgModificationTime()
    {
        if ($this->fsAdapter()) {
            return $this->fsAdapter()->lastModified($this->getRelativeSvgPath());
        }

        return filemtime($this->getSvgFilePath());
    }

    public function copySvg(QRCode $to)
    {
        $content = $this->fs()->get(
            $this->getRelativeSvgPath()
        );

        $this->fs()
            ->put(
                static::ofQRCode($to)->getRelativeSvgPath(),
                $content
            );
    }

    public function getSvgUrl()
    {
        $url = 'api' . static::SERVE_QRCODE_SVG_FILE_ROUTE;

        $url = str_replace('{qrcode}', $this->qrcode->id, $url);

        return url($url);
    }

    public function serveSvgFile()
    {
        $content = $this->fs()->get($this->getRelativeSvgPath());

        return static::serveSvgContent($content);
    }

    public static function serveSvgContent($content)
    {
        return [
            'content' => base64_encode($content)
        ];
    }

    public static function registerDirectSvgRoute()
    {
        Route::get(
            static::SERVE_QRCODE_SVG_FILE_ROUTE,
            function (QRCode $qrcode, Request $request) {
                return static::ofQRCode($qrcode)->serveDirectSvgFile();
            }
        )->name(static::directSvgRouteName());
    }

    public static function directSvgRouteName()
    {
        return 'qrcodeStorage.directSvg';
    }

    public function getTemporaryDirectUrl()
    {
        return URL::temporarySignedRoute(
            $this::directSvgRouteName(),
            now()->addMinutes(1),
            ['qrcode' => $this->qrcode],
            false
        );
    }

    private function serveDirectSvgFile()
    {
        $content = $this->fs()->get($this->getRelativeSvgPath());

        return response($content)->header('Content-Type', 'image/svg+xml');
    }

    public function getRelativeSvgPath()
    {
        return $this->getSvgFilePath(fullPath: false);
    }

    private function getSvgFilePath($fullPath = true)
    {
        $fileName = $this->qrcode->file_name;

        $path = config('qrcode.storage_path') . "/{$fileName}.svg";

        if ($fullPath) {
            return Storage::path($path);
        }

        return $path;
    }

    public function getContent()
    {
        return $this->fs()->get($this->getRelativeSvgPath());
    }
}
