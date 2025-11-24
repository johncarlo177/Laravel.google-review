<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use Illuminate\Support\Facades\Log;

class WhatsappMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['mobile_number', 'message'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = $this->qrcode->data->$var;
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
        $message =  trim($message);

        return urlencode($message);
    }
}
