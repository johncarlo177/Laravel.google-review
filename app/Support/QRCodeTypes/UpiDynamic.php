<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;


class UpiDynamic extends BaseDynamicType
{
    public static function name(): string
    {
        return t('Upi Dynamic');
    }

    public static function slug(): string
    {
        return 'upi-dynamic';
    }

    public function rules(): array
    {
        return [
            'payee_name' => 'required',
            'upi_id' => 'required',
            'amount' => 'numeric',
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return sprintf('%s %s', t('UPI to'), @$qrcode->data->upi_id);
    }
}
