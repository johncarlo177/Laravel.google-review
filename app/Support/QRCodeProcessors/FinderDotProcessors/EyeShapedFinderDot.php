<?php

namespace App\Support\QRCodeProcessors\FinderDotProcessors;

class EyeShapedFinderDot extends BaseFinderDotProcessor
{
    protected $id = 'eye-shaped';

    protected $shouldFlip = true;

    protected function pathCommands()
    {
        return 'm 205.01066,301.67021 v 96.65956 c 0,53.16448 43.49508,96.65956 96.65955,96.65956 H 494.98933 V 301.67021 c 0,-53.16448 -43.49507,-96.65955 -96.65956,-96.65955 H 205.01066 Z';
    }

    protected function pathScale()
    {
        return 1;
    }
}
