<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;



class Sms extends BaseType
{
    public static function name(): string
    {
        return t('SMS');
    }

    public static function slug(): string
    {
        return 'sms';
    }

    public function rules(): array
    {
        return [
            'phone' => 'required',
            'message' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['phone', 'message'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        return "SMSTO:$phone:$message";
    }

    public function generateName(QRCode $qrcode): string
    {
        return t('SMS to ') . $qrcode->data->phone;
    }
}
