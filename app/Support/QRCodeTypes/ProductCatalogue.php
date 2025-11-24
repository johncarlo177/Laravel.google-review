<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Models\QRCodeRedirect;

class ProductCatalogue extends BaseDynamicType
{
    public static function name(): string
    {
        return t('Product Catalogue');
    }

    public static function slug(): string
    {
        return 'product-catalogue';
    }

    public function rules(): array
    {
        return [
            'business_name' => 'required',
            'website' => 'url'
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return sprintf(
            '%s - %s',
            $qrcode->data->business_name,
            t('Product Catalogue')
        );
    }

    public function shouldCacheView()
    {
        return false;
    }
}
