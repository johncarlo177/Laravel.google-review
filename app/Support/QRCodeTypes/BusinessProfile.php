<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class BusinessProfile extends BaseDynamicType
{
    public static function name(): string
    {
        return t('Business Profile');
    }

    public static function slug(): string
    {
        return 'business-profile';
    }

    public function rules(): array
    {
        return [
            'businessName' => 'required',
            'businessDescription' => 'required',
            'businessType' => 'required',
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return $qrcode->data->businessName;
    }
}
