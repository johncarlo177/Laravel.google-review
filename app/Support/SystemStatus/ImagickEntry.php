<?php

namespace App\Support\SystemStatus;

class ImagickEntry extends BaseEntry
{
    public function title()
    {
        return 'Imagick Extension';
    }

    protected function instructionsText()
    {
        return 'Enable imagick extension in php.ini or contact your hosting provider to enable it.';
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
        return class_exists('Imagick');
    }
}
