<?php

namespace App\Support\PaymentProcessors\Traits;

use App\Models\SubscriptionPlan;

trait MapsPlanFields
{
    abstract public function config($key);

    protected function getMappedPlanField(SubscriptionPlan $plan, $fieldName)
    {
        return $this->config(
            sprintf('subscription_plan_%s_%s', $plan->id, $fieldName)
        );
    }
}
