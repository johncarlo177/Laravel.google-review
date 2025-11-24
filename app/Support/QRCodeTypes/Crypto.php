<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class Crypto extends BaseType
{
    public static function slug(): string
    {
        return 'crypto';
    }

    public static function name(): string
    {
        return t('Crypto');
    }

    public function rules(): array
    {
        return [
            'coin' => 'required',
            'address' => 'required'
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = [
            'coin',
            'address',
            'amount',
            'message',
        ];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = trim($qrcode->data->$var);
            }
        }

        $result = "$coin:$address?amount=$amount&message=$message";

        return $result;
    }
}
