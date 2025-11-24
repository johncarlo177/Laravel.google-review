<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;

class TextMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return !empty($this->qrcode->data->text);
    }

    protected function makeData(): string
    {
        return $this->qrcode->data->text;
    }
}
