<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class Zoom extends BaseType
{
    public static function name(): string
    {
        return t('Zoom');
    }

    public static function slug(): string
    {
        return 'zoom';
    }

    public function rules(): array
    {
        return [
            'meeting_id' => 'required',
            'meeting_password' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = [
            'meeting_id',
            'meeting_password',
        ];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = trim($qrcode->data->$var);
            }
        }

        $data = [
            'pwd' => $meeting_password,
        ];

        $url = "https://zoom.us/j/$meeting_id";

        $query = http_build_query($data);

        $result = "$url?$query";

        return $result;
    }
}
