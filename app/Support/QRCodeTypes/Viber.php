<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\System\Traits\WriteLogs;

class Viber extends BaseType
{
    use WriteLogs;

    public static function name(): string
    {
        return t('Viber');
    }

    public static function slug(): string
    {
        return 'viber';
    }

    public function rules(): array
    {
        return [
            'viber_number' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['viber_number'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = $qrcode->data->$var;
            }
        }

        $viber_number = trim($viber_number);

        $viber_number = str_replace(' ', '', $viber_number);

        $viber_number = str_replace('+', '00', $viber_number);

        $viber_number = urlencode($viber_number);

        $result = "viber://chat/?number=$viber_number";

        $this->logDebug($result);

        return $result;
    }
}
