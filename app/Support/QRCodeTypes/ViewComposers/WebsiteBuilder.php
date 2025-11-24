<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Support\GrapesJsStorageManager;

class WebsiteBuilder extends Base
{
    public function storage(): GrapesJsStorageManager
    {
        return (new GrapesJsStorageManager)->withQRCode($this->getQRCode());
    }

    public static function type()
    {
        return 'website-builder';
    }

    public function stripBodyTag($html)
    {
        return preg_replace('/<\/?body>/', '', $html);
    }

    public function html()
    {
        $html = json_decode($this->storage()->load('html'));

        return $this->stripBodyTag($html);
    }

    public function css()
    {
        return json_decode($this->storage()->load('css'));
    }
}
