<?php

namespace App\Support\QRCodeTypes\ViewComposers\Components\QRCodeFavicon;

use App\Support\QRCodeTypes\ViewComposers\Base as QRCodeComposer;

class Component
{
    private QRCodeComposer $composer;

    private FileServer $server;

    public function __construct(QRCodeComposer $composer)
    {
        $this->composer = $composer;

        $this->server = new FileServer($composer->getQRCode());
    }

    public function staticUrl($fileName)
    {
        return $this->server->route($fileName);
    }

    public function fileUrl($fileName)
    {
        $fileExists = $this->composer->fileUrl($fileName);

        if (!$fileExists) return null;

        return $this->server->route($fileName);
    }
}
