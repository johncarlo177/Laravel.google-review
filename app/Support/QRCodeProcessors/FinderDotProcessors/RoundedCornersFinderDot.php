<?php

namespace App\Support\QRCodeProcessors\FinderDotProcessors;

class RoundedCornersFinderDot extends BaseFinderDotProcessor
{
    protected $id = 'rounded-corners';

    protected function pathCommands()
    {
        return 'm 233.83773,490.33871 h 232.32579 c 13.35003,0 24.17152,-10.82149 24.17152,-24.17152 l 0.004,-232.33439 c 0,-13.35002 -10.82151,-24.17152 -24.17152,-24.17152 H 233.83248 c -13.35002,0 -24.17153,10.8215 -24.17153,24.17152 v 232.32578 c 0.004,13.35347 10.82494,24.17497 24.17152,24.17497 z';
    }
}
