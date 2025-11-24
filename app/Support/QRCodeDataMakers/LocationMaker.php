<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;


class LocationMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = ['longitude', 'latitude', 'application'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = trim($this->qrcode->data->$var);
            }
        }

        switch ($application) {
            case 'google_maps':
                $result = sprintf(
                    'https://www.google.com/maps/search/?api=1&query=%s',
                    urlencode("$latitude,$longitude")
                );
                break;

            case 'waze':
                $result = sprintf(
                    'https://www.waze.com/ul?ll=%s&navigate=yes&zoom=17',
                    urlencode("$latitude,$longitude")
                );
                break;

            default:
                $result = "geo:$latitude,$longitude";
                break;
        }

        return $result;
    }
}
