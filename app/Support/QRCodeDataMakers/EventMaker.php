<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use Carbon\Carbon;


class EventMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = [
            'created_at',
            'event_name',
            'organizer_name',
            'organizer_email',
            'location',
            'website',
            'starts_at',
            'ends_at',
            'description',
            'frequency',
            'longitude',
            'latitude',
            'timezone'
        ];

        foreach ($vars as $var) {
            $data[$var] = '';
            if (isset($this->qrcode->data->$var)) {
                $data[$var] = trim($this->qrcode->data->$var);
            }
        }

        $data['starts_at'] = $this->makeDateTime($data['starts_at'], $data['timezone']);
        $data['ends_at'] = $this->makeDateTime($data['ends_at'], $data['timezone']);

        $created_at = $this->qrcode->created_at ?: time();

        $data['created_at'] = $this->makeDateTime($created_at);

        $view = view('qrcode-content.event', $data);

        $result = $view->render();

        return $result;
    }

    private function makeDateTime($string, $timezone = null)
    {
        $date = (new Carbon($string))->format('Ymd\THis\Z');

        if (!$timezone) {
            return ":$date";
        }

        return ";TZID=$timezone:$date";
    }
}
