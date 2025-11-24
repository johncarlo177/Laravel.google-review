<?php

namespace App\Listeners;

use App\Events\PaymentGatewaySaved;
use App\Models\PaymentGateway;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class OnPaymentGatewaySaved implements ShouldQueue
{
    private PaymentGateway $paymentGateway;

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
     * @param  \App\Events\PaymentGatewaySaved  $event
     * @return void
     */
    public function handle(PaymentGatewaySaved $event)
    {
        $this->paymentGateway = $event->paymentGateway;

        $paymentRepository = $this->paymentGateway->resolveRepository();

        if (!$paymentRepository) return;

        $paymentRepository->boot();

        $this->saveSubscriptionPlanIds();
    }

    private function saveSubscriptionPlanIds()
    {
        $paymentRepository = $this->paymentGateway->resolveRepository();

        $model = $this->paymentGateway->make();

        if (!$model->enabled) {
            return;
        }

        $plans = SubscriptionPlan::all();

        foreach ($plans as $plan) {
            try {
                if ($plan->is_trial) continue;

                $paymentRepository->saveSubscriptionPlan($plan, true);

                $plan->save();
            } catch (\Throwable $th) {
                Log::error('Could not save subscription plan ID ' . $th->getMessage());
                Log::error($th->getTraceAsString());
            }
        }
    }
}
