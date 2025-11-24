<?php

namespace App\Support\SystemStatus;

class MbstringEntry extends BaseEntry
{
    public function title()
    {
        return 'MB String Extension';
    }

    protected function instructionsText()
    {
        return 'Enable mbstring extension in php.ini or contact your hosting provider to enable it.';
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
        return function_exists('mb_strcut');
    }
}
