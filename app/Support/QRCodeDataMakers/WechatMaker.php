<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use Illuminate\Support\Facades\Log;

class WechatMaker extends BaseMaker implements QRCodeDataMaker
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

        $result = "weixin://dl/chat?$username";

        Log::debug($result);

        return $result;
    }
}
