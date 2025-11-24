<?php

namespace App\Jobs;

use App\Support\PaymentProcessors\Interfaces\CanSyncSubscriptionPlans;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSubscriptionPlans implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $paymentProcessorSlug;

    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $paymentProcessorSlug)
    {
        $this->paymentProcessorSlug = $paymentProcessorSlug;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $paymentProcessorManager = new PaymentProcessorManager();

        $processor = $paymentProcessorManager->getBySlug($this->paymentProcessorSlug);

        if ($processor instanceof CanSyncSubscriptionPlans) {
            $processor->syncPlans();
        }
    }

    public function uniqueId()
    {
        return $this->paymentProcessorSlug;
    }
}
