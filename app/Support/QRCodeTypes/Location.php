<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;

class Location extends BaseType
{
    public static function slug(): string
    {
        return 'location';
    }

    public static function name(): string
    {
        return t('Location');
    }

    public function rules(): array
    {
        return [
            'longitude' => 'required',
            'latitude' => 'required',
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $vars = ['longitude', 'latitude', 'application'];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($qrcode->data->$var)) {
                $$var = trim($qrcode->data->$var);
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
