<?php

namespace App\Listeners;

use App\Events\CurrencyEnabled;
use App\Interfaces\CurrencyManager;
use App\Models\PaymentGateway;
use App\Models\SubscriptionPlan;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class OnCurrencyEnabled implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CurrencyEnabled $event)
    {
        $manager = new PaymentProcessorManager();

        $manager->syncPlans();
    }
}
