<?php

namespace App\Support\System\Queue;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Queue\Worker as QueueWorker;
use Illuminate\Queue\WorkerOptions;

class Worker extends QueueWorker
{
    use WriteLogs;

    protected function registerTimeoutHandler($job, WorkerOptions $options) {}

    protected function resetTimeoutHandler() {}

    protected function listenForSignals() {}
}
