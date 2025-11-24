<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class FaceTime extends BaseType
{
    public static function slug(): string
    {
        return 'facetime';
    }

    public static function name(): string
    {
        return t('Face Time');
    }

    public function rules(): array
    {
        return [
            'target' => 'required'
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['target'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        $target = trim($target);

        $result = "facetime:$target";

        return $result;
    }
}
