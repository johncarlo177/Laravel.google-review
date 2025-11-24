<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;

class SmsMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['phone', 'message'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = $this->qrcode->data->$var;
            }
        }

        return "SMSTO:$phone:$message";
    }
}
