<?php

namespace App\Support\QRCodeReports;

class ScansPerCity extends BaseReport
{
    public function slug(): string
    {
        return 'scans-per-city';
    }

    protected function reportColumn()
    {
        return 'city';
    }
}
