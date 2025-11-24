<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\QRCodeTypes\ViewComposers\VCardPlus\VCardFileGenerator;

class vCard extends BaseType
{
    public static function name(): string
    {
        return t('vCard');
    }

    public static function slug(): string
    {
        return 'vcard';
    }

    public function rules(): array
    {
        return [
            'firstName' => 'required',
            'lastName' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        return VCardFileGenerator::withDataProvider(
            fn($name) => @$qrcode->data->{$name}
        )
            ->withoutFallbackWebsite()
            ->vcard();
    }

    public function generateName(QRCode $qrcode): string
    {
        return t('vCard of ') . $qrcode->data->firstName;
    }
}
