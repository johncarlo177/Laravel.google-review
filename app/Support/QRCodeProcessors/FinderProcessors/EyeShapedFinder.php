<?php

namespace App\Support\QRCodeProcessors\FinderProcessors;

class EyeShapedFinder extends BaseFinderProcessor
{
    protected $id = 'eye-shaped';

    protected $shouldFlip = true;

    protected function pathCommands()
    {
        return 'm 8.7639845,233.53442 v 229.5036 c 0,126.23106 103.2725155,229.50359 229.5035855,229.50359 H 697.27475 V 233.53442 C 697.27475,107.30335 594.00223,4.0308445 467.77117,4.0308445 H 8.7639845 Z M 146.4641,90.094684 h 321.30502 c 79.18079,0 143.43974,64.258946 143.43974,143.439736 V 606.47776 H 238.26553 c -79.18078,0 -143.439744,-64.25896 -143.439744,-143.43974 V 90.094684 Z';
    }
}
