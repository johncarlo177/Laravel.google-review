<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use Illuminate\Support\Facades\Log;

class VcardMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = [
            'firstName',
            'lastName',
            'mobile',
            'phone',
            'fax',
            'email',
            'company',
            'job',
            'street',
            'city',
            'zip',
            'state',
            'country',
            'website'
        ];

        $data = [];

        foreach ($vars as $var) {
            $data[$var] = '';

            if (isset($this->qrcode->data->$var)) {
                $data[$var] = $this->qrcode->data->$var;
            }
        }

        $vcard = view('qrcode-content.vcard', $data);

        return $vcard->render();
    }
}
