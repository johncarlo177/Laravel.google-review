<?php

namespace App\Support\SystemStatus;

class PHPVersionEntry extends BaseEntry
{
    public function __construct()
    {
    }

    protected function instructionsText()
    {
        return 'On end of July 2024 we will be upgrading the software to Laravel v11, which requires <strong>PHP 8.2</strong>, please upgrade your PHP version at the earliest.';
    }

    protected function informationText()
    {
        return 'PHP version is supported';
    }

    public function title()
    {
        return 'PHP Version';
    }

    public function text()
    {
        return $this->getVersion();
    }

    protected function isSuccess()
    {
        return $this->getVersion() >= 8.2;
    }

    private function getVersion()
    {
        return phpversion();
    }

    public function sortOrder()
    {
        return 19;
    }
}
