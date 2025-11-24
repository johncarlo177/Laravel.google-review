<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\SubscriptionPlan;

class ColorCustomizationFeatureSeeder extends Seeder
{
    protected $version = 'v2.26.2';

    protected function run()
    {
        $plans = SubscriptionPlan::all();

        $plans->each(function (SubscriptionPlan $plan) {

            $plan->features = array_merge(

                $plan->features,

                array(
                    'qrcode.color_customization'
                )

            );

            $plan->save();
        });
    }
}
