<?php

namespace App\Support\QRCodeReports;

class ScansPerBrowser extends BaseReport
{
    public function slug(): string
    {
        return 'scans-per-browser';
    }

    protected function reportColumn()
    {
        return 'browser';
    }
}
