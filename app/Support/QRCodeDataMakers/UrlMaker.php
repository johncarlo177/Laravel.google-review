<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use App\Models\QRCodeRedirect;
use Illuminate\Support\Facades\Log;

class UrlMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        $redirect = QRCodeRedirect::where('qrcode_id', $this->qrcode->id)->first();

        return !empty($redirect) || !empty($this->qrcode->data->url);
    }

    protected function makeData(): string
    {
        $redirect = QRCodeRedirect::where('qrcode_id', $this->qrcode->id)->first();

        if ($redirect) {
            return $redirect->route;
        }

        if (isset($this->qrcode->data->url)) {
            return $this->qrcode->data->url;
        }
    }
}
