<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;


class TelegramMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['username'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = $this->qrcode->data->$var;
            }
        }

        $username = trim($username);

        $result = "https://telegram.me/$username";

        return $result;
    }
}
