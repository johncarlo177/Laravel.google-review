<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use Illuminate\Support\Str;

class Text extends BaseType
{
    public static function name(): string
    {
        return t('Text');
    }

    public static function slug(): string
    {
        return 'text';
    }

    public function rules(): array
    {
        return [
            'text' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['text'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        return $text;
    }

    public function generateName(QRCode $qrcode): string
    {
        return Str::limit($qrcode->data->text, 50);
    }
}
