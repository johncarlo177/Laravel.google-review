<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class WiFi extends BaseType
{
    public static function name(): string
    {
        return t('WiFi');
    }

    public static function slug(): string
    {
        return 'wifi';
    }

    public function rules(): array
    {
        return [
            'ssid' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['type', 'ssid', 'password', 'hidden'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        $hidden = $hidden ? 'true' : 'false';

        return "WIFI:T:$type;S:$ssid;P:$password;H:$hidden;";
    }

    public function generateName(QRCode $qrcode): string
    {
        return t('WiFi') . ' ' . $qrcode->data->ssid;
    }
}
