<?php

namespace App\Listeners;

use App\Events\SubscriptionPlanSaved;
use App\Models\SubscriptionPlan;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnSubscriptionPlanSaved implements ShouldQueue
{
    private PaymentProcessorManager $paymentProcessorManager;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->paymentProcessorManager = new PaymentProcessorManager;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SubscriptionPlanSaved  $event
     * @return void
     */
    public function handle(SubscriptionPlanSaved $event)
    {
        if (app()->runningInConsole()) {
            return;
        }

        $this->paymentProcessorManager->syncPlan($event->plan);
    }
}
