<?php

namespace App\Support\SystemStatus;

use Throwable;

class AllowUrlFOpenEntry extends BaseEntry
{
    protected function instructionsText()
    {
        return
            sprintf(
                'Ask your server administrator to enable allow_url_fopen by adding the following line in php %s configurations. <code>allow_url_fopen=1</code>',
                phpversion()
            );
    }

    protected function informationText()
    {
        return 'allow_url_fopen is enabled.';
    }

    protected function isSuccess()
    {
        return ini_get('allow_url_fopen') > 0;
    }

    public function title()
    {
        return 'allow_url_fopen';
    }

    public function text()
    {
        return $this->isSuccess() ? 'Enabled' : 'Disabled';
    }
}
