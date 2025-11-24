<?php

namespace App\Support\QRCodeTypes;

use App\Interfaces\DeviceInfo;
use App\Models\QRCode;

class AppDownload extends BaseDynamicType
{
    private DeviceInfo $info;

    public function __construct()
    {
        parent::__construct();

        $this->info = app(DeviceInfo::class);
    }


    public static function name(): string
    {
        return t('App Download');
    }

    public static function slug(): string
    {
        return 'app-download';
    }

    public function rules(): array
    {
        return [
            'appName' => 'required',
            'appDescription' => 'required',
            'google_play_url' => 'url',
            'apple_store_url' => 'url',
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return $qrcode->data->appName;
    }

    private function googlePlayUrl(QRCode $qrcode)
    {
        return $qrcode->data?->google_play_url;
    }

    private function appleStoreUrl(QRCode $qrcode)
    {
        return $qrcode->data?->apple_store_url;
    }

    public function renderView(QRCode $qrcode)
    {
        if ($this->info->isAndroid() && $this->googlePlayUrl($qrcode)) {
            return redirect($this->googlePlayUrl($qrcode));
        }

        if ($this->info->is_iPhone() && $this->appleStoreUrl($qrcode)) {
            return redirect($this->appleStoreUrl($qrcode));
        }

        return parent::renderView($qrcode);
    }
}
