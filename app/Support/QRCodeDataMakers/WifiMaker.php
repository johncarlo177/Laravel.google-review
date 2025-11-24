<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;

class WifiMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['type', 'ssid', 'password', 'hidden'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = $this->qrcode->data->$var;
            }
        }

        $hidden = $hidden ? 'true' : 'false';

        return "WIFI:T:$type;S:$ssid;P:$password;H:$hidden;";
    }
}
