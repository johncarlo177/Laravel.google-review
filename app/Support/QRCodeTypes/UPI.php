<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\UPIPaymentUrlGenerator;

class UPI extends BaseType
{
    public static function name(): string
    {
        return t('UPI');
    }

    public static function slug(): string
    {
        return 'upi';
    }

    public function rules(): array
    {
        return [
            'payee_name' => 'required',
            'upi_id' => 'required',
            'amount' => 'numeric',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $payee_name = @$qrcode->data?->payee_name;
        $upi_id = @$qrcode->data?->upi_id;
        $amount = @$qrcode->data?->amount;

        return UPIPaymentUrlGenerator::withUpiId($upi_id)
            ->withAmount($amount)
            ->withMerchantName($payee_name)
            ->generateUrl();
    }

    public function generateName(QRCode $qrcode): string
    {
        return sprintf('%s %s', t('UPI to'), @$qrcode->data->upi_id);
    }
}
