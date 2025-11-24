<?php

namespace App\Support\SystemStatus;

class DOMDocumentEntry extends BaseEntry
{
    protected function instructionsText()
    {
        return 'Enable Dom Document PHP extension.';
    }

    protected function informationText()
    {
        return 'Dom Document is enabled.';
    }

    protected function isSuccess()
    {
        return class_exists('DOMDocument');
    }

    public function title()
    {
        return 'Dom Document Extension';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Enabled' : 'Disabled';
    }
}
