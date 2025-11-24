<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class Email extends BaseType
{
    public static function slug(): string
    {
        return 'email';
    }

    public static function name(): string
    {
        return t('Email');
    }

    public function rules(): array
    {
        return [
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['email', 'subject', 'message'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        $message = str_replace("\n", '%0D%0A', $message);

        $email = str_replace(';', ',', $email);

        $email = str_replace(' ', '', $email);

        $encoded = "mailto:$email?subject=$subject&body=$message";

        return $encoded;
    }

    public function generateName(QRCode $qrcode): string
    {
        return t('Email to') . ' ' . $qrcode->data->email;
    }
}
