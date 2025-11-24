<?php

namespace App\Support\QRCodeReports;

class ScansPerCountry extends BaseReport
{
    public function slug(): string
    {
        return 'scans-per-country';
    }

    protected function reportColumn()
    {
        return 'country';
    }
}
