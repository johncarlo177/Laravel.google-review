<?php

namespace App\Support\SystemStatus;

class FPassThroughEntry extends BaseEntry
{
    protected function instructionsText()
    {
        return 'Enable fpassthru PHP function, user uploads (logo and QR code files) will not work unless this function is enabled.';
    }

    protected function informationText()
    {
        return 'fpassthru is used to serve uploaded files.';
    }

    protected function isSuccess()
    {
        return function_exists('fpassthru');
    }

    public function title()
    {
        return 'fpassthru function';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Enabled' : 'Disabled';
    }
}
