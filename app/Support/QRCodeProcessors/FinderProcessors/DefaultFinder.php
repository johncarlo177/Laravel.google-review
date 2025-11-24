<?php

namespace App\Support\QRCodeProcessors\FinderProcessors;

class DefaultFinder extends BaseFinderProcessor
{
    protected $id = 'default';

    protected function pathCommands()
    {
        return 'M 0 0 L 0 933.33398 L 933.33398 933.33398 L 933.33398 0 L 0 0 z M 133.33398 133.33398 L 800 133.33398 L 800 800 L 133.33398 800 L 133.33398 133.33398 z';
    }

    protected function pathScale()
    {
        return 0.75;
    }
}
