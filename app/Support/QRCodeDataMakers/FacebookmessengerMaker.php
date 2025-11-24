<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;


class FacebookmessengerMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['facebook_page_name'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = $this->qrcode->data->$var;
            }
        }

        $facebook_page_name = trim($facebook_page_name);

        if (preg_match('/https?:\/\/m\.me/', $facebook_page_name)) {
            $result = $facebook_page_name;
        } else {
            $result = "https://m.me/$facebook_page_name";
        }

        return $result;
    }
}
