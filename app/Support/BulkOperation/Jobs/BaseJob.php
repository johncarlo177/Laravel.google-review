<?php

namespace App\Support\BulkOperation\Jobs;

use App\Models\BulkOperationInstance;
use App\Support\BulkOperation\BaseBulkOperation;
use App\Support\BulkOperation\BulkOperationManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class BaseJob implements ShouldQueue
{
    use WriteLogs;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected BulkOperationManager $operations;

    public function __construct()
    {
        $this->operations = app(BulkOperationManager::class);
    }

    public function handle()
    {
        $this->logDebug('Running Job');

        try {
            $this->run();
        } catch (Throwable $th) {
            $this->logDebug('Job completed with errors ' . $th->getMessage());
            $this->logDebug($th->getTraceAsString());
        }

        $this->afterRun();
    }

    protected abstract function run();

    protected abstract function getOperationInstance(): BulkOperationInstance;

    protected abstract function getOperation(): BaseBulkOperation;

    protected function afterRun()
    {
        $operation = $this->getOperation();

        $instance = $this->getOperationInstance();

        $operation->updateInstanceStatus(
            $instance
        );

        if ($operation->isCompleted($instance)) {

            $operation->onOperationCompleted($instance);
        }
    }
}
