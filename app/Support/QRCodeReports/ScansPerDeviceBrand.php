<?php

namespace App\Support\QRCodeReports;

class ScansPerDeviceBrand extends BaseReport
{
    public function slug(): string
    {
        return 'scans-per-device-brand';
    }

    protected function reportColumn()
    {
        return 'device_brand';
    }
}
