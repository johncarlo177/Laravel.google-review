<?php

namespace App\Support\Webhooks;

use App\Models\QRCodeScan;


class QRCodeScanDispatcher extends BaseDispatcher
{
    protected QRCodeScan $scan;

    protected function event()
    {
        return 'qrcode_scan';
    }

    public static function withScan(QRCodeScan $scan)
    {
        $instance = new static;

        $instance->scan = $scan;

        return $instance;
    }

    protected function getPayloadType()
    {
        return 'QRCodeScan';
    }

    protected function getPayload()
    {
        $array = $this->scan->toArray();

        return $array;
    }
}
