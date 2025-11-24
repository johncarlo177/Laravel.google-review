<?php

namespace App\Support\PaymentProcessors\Traits;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Log;
use LogicException;
use Throwable;

trait HandlesSyncSubscriptionPlans
{
    public function resolveInterval(SubscriptionPlan $plan)
    {
        if ($plan->isYearly()) {
            return $this->yearlyInterval();
        }

        if ($plan->isMonthly()) {
            return $this->monthlyInterval();
        }

        throw new LogicException("Plan is neither monthly nor yearly, if new interval is added recently it must be resolved in HandlesSyncSubscriptionPlans::resolveInterval");
    }

    public function syncPlans()
    {
        $plans = SubscriptionPlan::all();

        $plans->each(fn($plan) => $this->syncPlan($plan));
    }

    public function syncPlan(SubscriptionPlan $plan)
    {
        if ($plan->is_trial) {
            Log::info(
                sprintf('Plan (%s) is trial, skipping sync.', $plan->name)
            );
        }

        Log::info(
            sprintf('Syncing plan (%s) with %s.', $plan->name, $this->slug())
        );

        try {
            $id = $this->syncSubscriptionPlan($plan);

            Log::info(
                sprintf('Completed sycning plan (%s) with %s id = %s', $plan->name, $this->slug(), $id)
            );
        } catch (Throwable $ex) {
            Log::error(
                sprintf('Sync plan (%s) failed, payment processor: %s. %s', $plan->name, $this->slug(), $ex->getMessage())
            );

            Log::debug($ex->getMessage());

            Log::debug($ex->getTraceAsString());
        }
    }

    /**
     * @return string remote subscription plan id
     */
    protected abstract function syncSubscriptionPlan(SubscriptionPlan $plan): string;
}
