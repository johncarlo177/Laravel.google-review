<?php

namespace App\Support\System\Queue;

use Illuminate\Queue\Console\WorkCommand as ConsoleWorkCommand;
use Illuminate\Contracts\Cache\Repository as Cache;


class WorkCommand extends ConsoleWorkCommand
{
    public function __construct(Cache $cache)
    {
        parent::__construct(app(Worker::class), $cache);
    }
}
