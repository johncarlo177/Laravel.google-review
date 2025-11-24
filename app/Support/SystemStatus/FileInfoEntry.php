<?php

namespace App\Support\SystemStatus;

class FileInfoEntry extends BaseEntry
{
    protected function instructionsText()
    {
        return 'Enable File Info PHP extension.';
    }

    protected function informationText()
    {
        return 'File Info is enabled.';
    }

    protected function isSuccess()
    {
        return class_exists('finfo');
    }

    public function title()
    {
        return 'File Info Extension';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Enabled' : 'Disabled';
    }
}
