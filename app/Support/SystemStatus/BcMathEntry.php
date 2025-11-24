<?php

namespace App\Support\SystemStatus;

class BcMathEntry extends BaseEntry
{
    public function title()
    {
        return 'BC Math Extension';
    }

    protected function instructionsText()
    {
        return 'Enable bcmath extension in php.ini or contact your hosting provider to enable it.';
    }

    protected function informationText()
    {
        return 'Extension detected';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Installed' : 'Not installed';
    }

    protected function isSuccess()
    {
        return function_exists('bcmul');
    }
}
