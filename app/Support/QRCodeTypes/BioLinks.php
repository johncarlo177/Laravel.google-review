<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class BioLinks extends BaseDynamicType
{
    public static function slug(): string
    {
        return 'biolinks';
    }

    public static function name(): string
    {
        return t('Bio Links (List of Links)');
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'email'
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return $qrcode->data->name;
    }
}
