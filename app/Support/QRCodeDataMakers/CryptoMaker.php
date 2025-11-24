<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;


class CryptoMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = [
            'coin',
            'address',
            'amount',
            'message',
        ];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = trim($this->qrcode->data->$var);
            }
        }

        $result = "$coin:$address?amount=$amount&message=$message";

        return $result;
    }
}
