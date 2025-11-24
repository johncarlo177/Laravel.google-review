<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class WhatsApp extends Url
{
    public static function name(): string
    {
        return t('WhatsApp');
    }

    public static function slug(): string
    {
        return 'whatsapp';
    }

    public function rules(): array
    {
        return [
            'mobile_number' => 'required',
        ];
    }

    public function makeDestination(QRCode $qrcode): string
    {
        $vars = ['mobile_number', 'message'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        $mobile_number = $this->filterMobileNumber($mobile_number);

        $message = $this->filterMessage($message);

        $result = "https://wa.me/$mobile_number?text=$message";

        return $result;
    }

    protected function filterMobileNumber($mobile_number)
    {
        $n = preg_replace('/[^\d]/', '', $mobile_number);

        return ltrim($n, '0');
    }

    protected function filterMessage($message)
    {
        $message = trim($message);

        return urlencode($message);
    }

    public function generateName(QRCode $qrcode): string
    {
        return t('WhatsApp to: ') . $qrcode->data->mobile_number;
    }
}
