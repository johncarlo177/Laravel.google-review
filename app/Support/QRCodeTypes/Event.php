<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\System\Traits\WriteLogs;
use Carbon\Carbon;
use Illuminate\Validation\Validator;

class Event extends BaseDynamicType
{
    use WriteLogs;

    public static function slug(): string
    {
        return 'event';
    }

    public static function name(): string
    {
        return t('Event');
    }

    public function rules(): array
    {
        return [
            'event_name' => 'required',
            'description' => 'required',
            'organizer_name' => 'required',
            'contact_email' => 'email',
            'day_breakdown' => 'required',
        ];
    }

    protected function extendValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $breakdown = @$validator->getData()['day_breakdown'];

            // TODO: must validate all breakdown timing are correct
        });
    }

    public function _makeData(QRCode $qrcode): string
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
            if (isset($qrcode->data->$var)) {
                $data[$var] = trim($qrcode->data->$var);
            }
        }

        $data['starts_at'] = $this->makeDateTime($data['starts_at'], $data['timezone']);
        $data['ends_at'] = $this->makeDateTime($data['ends_at'], $data['timezone']);

        $created_at = $qrcode->created_at ?: time();

        $data['created_at'] = $this->makeDateTime($created_at);

        $view = view('qrcode-content.event', $data);

        $result = $view->render();

        $this->logDebug($result);

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

    public function generateName(QRCode $qrcode): string
    {
        return t('Event: ') . $qrcode->data->event_name;
    }
}
