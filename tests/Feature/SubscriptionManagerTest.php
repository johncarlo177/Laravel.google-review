<?php

namespace Tests\Feature;

use App\Events\SubscriptionVerified;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionStatus;
use App\Models\User;
use App\Repositories\SubscriptionManager;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * @group tested
 */
class SubscriptionManagerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_set_expired_subscriptions()
    {
        $user = User::all()->random();

        $plan = SubscriptionPlan::all()
            ->first(fn ($p) => $p->monthly_price > 0 && $p->is_trial == false);

        $subscription = new Subscription([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id
        ]);

        $subscription->save();

        sleep(1);

        event(new SubscriptionVerified($subscription));

        $subscription = Subscription::with('statuses')->find($subscription->id);

        $this->assertEquals(SubscriptionStatus::STATUS_ACTIVE, $subscription->statuses[0]->status);

        $date = Carbon::now()->subMonths(13);

        $subscription->statuses[0]->created_at = $date;

        $subscription->statuses[0]->save();

        $subscription->refresh();

        $this->assertEquals(
            $date->format('Y-m-d'),
            Carbon::parse($subscription->statuses[0]->created_at)->format('Y-m-d')
        );

        $subscriptions = new SubscriptionManager;

        $subscriptions->setExpiredSubscriptions();

        $subscription->refresh();

        $this->assertEquals(SubscriptionStatus::STATUS_EXPIRED, $subscription->statuses[0]->status);
    }

    public function test_onetime_plan()
    {
        $user = User::all()->random();

        $plan = new SubscriptionPlan();

        $plan->name = 'Test plan';

        $plan->frequency = SubscriptionPlan::FREQUENCY_ONE_TIME;

        $plan->price = 10;

        $plan->number_of_dynamic_qrcodes = 1000;

        $plan->number_of_scans = 1000;

        $plan->is_trial = false;

        $plan->save();

        $subscription = new Subscription([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id
        ]);

        $subscription->save();

        sleep(1);

        event(new SubscriptionVerified($subscription));

        $subscription = Subscription::with('statuses')->find($subscription->id);

        $this->assertEquals(SubscriptionStatus::STATUS_ACTIVE, $subscription->statuses[0]->status);

        $date = Carbon::now()->subMonths(13);

        $subscription->statuses[0]->created_at = $date;

        $subscription->statuses[0]->save();

        $subscription->refresh();

        $this->assertEquals(
            $date->format('Y-m-d'),
            Carbon::parse($subscription->statuses[0]->created_at)->format('Y-m-d')
        );

        $subscriptions = new SubscriptionManager;

        $subscriptions->setExpiredSubscriptions();

        $subscription->refresh();

        $this->assertEquals(SubscriptionStatus::STATUS_ACTIVE, $subscription->statuses[0]->status);
    }
}
