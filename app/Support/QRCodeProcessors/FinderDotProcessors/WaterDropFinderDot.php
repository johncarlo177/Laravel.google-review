<?php

namespace App\Support\QRCodeProcessors\FinderDotProcessors;

class WaterDropFinderDot extends BaseFinderDotProcessor
{
    protected $id = 'water-drop';

    protected $shouldFlip = true;

    protected function pathCommands()
    {
        return 'm 201.08108,200.89207 h 29.78409 185.3216 c 45.66977,0 82.7325,37.06359 82.7325,82.73335 v 82.32948 h -0.007 v 50.41967 c 0,45.66978 -37.06267,82.73335 -82.7325,82.73335 h -17.23549 v -0.37681 H 283.81407 c -45.66976,0 -82.73334,-37.06359 -82.73334,-82.73336 z';
    }
}
