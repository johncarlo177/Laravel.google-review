<?php

namespace App\Support\SystemStatus;

class ZipArchiveEntry extends BaseEntry
{
    public function title()
    {
        return 'ZipArchive Extension';
    }

    protected function instructionsText()
    {
        return 'Enable Zip Archive extension in php.ini or contact your hosting provider to enable it.';
    }

    protected function informationText()
    {
        return 'Extension detected';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Installed' : 'Not installed';
    }

    public function instructions()
    {
        return '';
    }

    protected function isSuccess()
    {
        return class_exists('ZipArchive');
    }
}
