<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class Telegram extends BaseType
{
    public static function name(): string
    {
        return t('Telegram');
    }

    public static function slug(): string
    {
        return 'telegram';
    }

    public function rules(): array
    {
        return [
            'username' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['username'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        $username = trim($username);

        $result = "https://telegram.me/$username";

        return $result;
    }
}
