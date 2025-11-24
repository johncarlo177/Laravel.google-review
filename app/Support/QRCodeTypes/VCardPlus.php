<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Models\QRCodeRedirect;


class VCardPlus extends BaseDynamicType
{
    public static function name(): string
    {
        return t('vCard Plus');
    }

    public static function slug(): string
    {
        return 'vcard-plus';
    }

    public function rules(): array
    {
        return [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'email',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $redirect = QRCodeRedirect::where('qrcode_id', $qrcode->id)->first();

        return $redirect->route;
    }

    public function generateName(QRCode $qrcode): string
    {
        return sprintf(
            '%s %s',
            $qrcode->data->firstName,
            $qrcode->data->lastName
        );
    }
}
