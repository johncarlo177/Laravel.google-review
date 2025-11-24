<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;

class EmailMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['email', 'subject', 'message'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = $this->qrcode->data->$var;
            }
        }

        $message = str_replace("\n", '%0D%0A', $message);

        $email = str_replace(';', ',', $email);

        $email = str_replace(' ', '', $email);

        $encoded = "mailto:$email?subject=$subject&body=$message";

        return $encoded;
    }
}
