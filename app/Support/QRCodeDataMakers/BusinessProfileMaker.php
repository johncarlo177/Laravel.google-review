<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use App\Models\QRCodeRedirect;
use Illuminate\Support\Facades\Log;

class BusinessProfileMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function rules()
    {
        return [
            'businessName' => 'required'
        ];
    }

    protected function makeData(): string
    {
        $redirect = QRCodeRedirect::where('qrcode_id', $this->qrcode->id)->first();

        return $redirect->route;
    }
}
