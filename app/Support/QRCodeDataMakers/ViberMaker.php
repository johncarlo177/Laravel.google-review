<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use Illuminate\Support\Facades\Log;

class ViberMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['viber_number'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = $this->qrcode->data->$var;
            }
        }

        $viber_number = trim($viber_number);

        $viber_number = str_replace(' ', '', $viber_number);

        $viber_number = str_replace('+', '00', $viber_number);

        $viber_number = urlencode($viber_number);

        $result = "viber://chat/?number=$viber_number";

        return $result;
    }
}
