<?php

namespace App\Support\QRCodeProcessors\FinderDotProcessors;

class DefaultFinderDot extends BaseFinderDotProcessor
{
    protected $id = 'default';

    protected function pathCommands()
    {
        return 'M 200,200 H 500 V 500 H 200 Z';
    }
}
