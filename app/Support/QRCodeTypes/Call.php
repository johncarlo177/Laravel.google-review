<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class Call extends BaseType
{
    public static function slug(): string
    {
        return 'call';
    }

    public static function name(): string
    {
        return t('Call');
    }

    public function rules(): array
    {
        return [
            'phone' => 'required'
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['phone'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        return "tel: $phone";
    }

    public function generateName(QRCode $qrcode): string
    {
        return t('Call to') . ' ' . $qrcode->data->phone;
    }
}
