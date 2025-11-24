<?php

namespace App\Support\QRCodeReports;

class ScansPerLanguage extends BaseReport
{
    public function slug(): string
    {
        return 'scans-per-language';
    }

    protected function reportColumn()
    {
        return 'language';
    }
}
