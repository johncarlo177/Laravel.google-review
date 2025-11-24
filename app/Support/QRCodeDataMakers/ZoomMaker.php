<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use Illuminate\Support\Facades\Log;

class ZoomMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = [
            'meeting_id',
            'meeting_password',
        ];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = trim($this->qrcode->data->$var);
            }
        }

        $data = [
            'pwd' => $meeting_password,
        ];

        $url = "https://zoom.us/j/$meeting_id";

        $query = http_build_query($data);

        $result = "$url?$query";

        return $result;
    }
}
