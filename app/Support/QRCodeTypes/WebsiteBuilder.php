<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class WebsiteBuilder extends BaseDynamicType
{
    public static function slug(): string
    {
        return 'website-builder';
    }

    public static function name(): string
    {
        return t('Website Builder');
    }

    public function rules(): array
    {
        return [
            'website_name' => 'required'
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return $qrcode->data?->website_name ?? $this::name();
    }
}
