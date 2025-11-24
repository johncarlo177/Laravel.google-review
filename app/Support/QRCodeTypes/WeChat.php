<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

/**
 * @deprecated To be removed in v2.13
 */
class WeChat extends BaseType
{
    public static function name(): string
    {
        return t('WeChat');
    }

    public static function slug(): string
    {
        return 'wechat';
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

        $result = "weixin://dl/chat?$username";

        return $result;
    }
}
