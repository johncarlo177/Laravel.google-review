<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class Messenger extends BaseType
{
    public static function name(): string
    {
        return t('Facebook Messenger');
    }

    public static function slug(): string
    {
        return 'facebookmessenger';
    }

    public function rules(): array
    {
        return [
            'facebook_page_name' => 'required'
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['facebook_page_name'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        $facebook_page_name = trim($facebook_page_name);

        if (preg_match('/https?:\/\/m\.me/', $facebook_page_name)) {
            $result = $facebook_page_name;
        } else {
            $result = "https://m.me/$facebook_page_name";
        }

        return $result;
    }
}
