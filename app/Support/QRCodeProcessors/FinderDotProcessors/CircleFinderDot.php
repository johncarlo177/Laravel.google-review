<?php

namespace App\Support\QRCodeProcessors\FinderDotProcessors;

class CircleFinderDot extends BaseFinderDotProcessor
{
    protected $id = 'circle';

    protected function pathCommands()
    {
        return 'M 516.8853,350 A 166.88531,166.88531 0 0 1 350,516.88531 166.88531,166.88531 0 0 1 183.11469,350 166.88531,166.88531 0 0 1 350,183.11469 166.88531,166.88531 0 0 1 516.8853,350 Z';
    }
}
