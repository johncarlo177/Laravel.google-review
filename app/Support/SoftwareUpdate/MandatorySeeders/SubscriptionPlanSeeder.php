<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\SubscriptionPlan;
use App\Support\SubscriptionPlansManager;
use App\Support\System\Traits\WriteLogs;

class SubscriptionPlanSeeder extends Seeder
{
    use WriteLogs;

    protected $version = 'v2.08/1';

    protected function run()
    {
        $manager = new SubscriptionPlansManager();

        $plans = SubscriptionPlan::get();

        $plans->each(function ($_plan) use ($manager) {
            /** @var SubscriptionPlan */
            $plan = $_plan;

            $plan->frequency = SubscriptionPlan::FREQUENCY_MONTHLY;

            $plan->price = $plan->monthly_price;

            $plan->save();

            if ($plan->is_trial) return;

            $clone = $manager->duplicate($plan);

            $clone->frequency = SubscriptionPlan::FREQUENCY_YEARLY;

            $clone->price = bcmul($clone->monthly_price, '12');

            $clone->save();
        });

        $this->debugVersion();
    }
}
