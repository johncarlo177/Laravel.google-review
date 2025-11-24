<?php

namespace Tests\Feature;

use App\Interfaces\SubscriptionManager;
use App\Models\QRCode;
use App\Models\QRCodeRedirect;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionStatus;
use App\Jobs\MakeSubscriptionsExpired;
use App\Models\QRCodeScan;
use Illuminate\Auth\Events\Registered;


/**
 * @group tested
 */
class ScanRestrictionsTest extends TestCase
{
    public function test_count_limit_reached()
    {
        list($user) = $this->newUserWithTestRole();

        $paidPlan = SubscriptionPlan::where('is_trial', false)->first();

        $paidPlan->number_of_scans = 10;

        $paidPlan->save();

        /** @var \App\Interfaces\SubscriptionManager */
        $subscriptionManager = app(SubscriptionManager::class);

        $subscriptionManager->saveSubscription([
            'subscription_plan_id' => $paidPlan->id,
            'user_id' => $user->id,
            'subscription_status' => SubscriptionStatus::STATUS_ACTIVE
        ]);

        $userSubscription = $user->subscriptions[0];

        $this->assertEquals(
            $paidPlan->id,
            $userSubscription->subscription_plan_id,
            'Paid subscription was not created for user.'
        );

        $qrcode = new QRCode();

        $qrcode->type = 'url';

        $qrcode->data = [
            'url' => 'https://google.com'
        ];

        $qrcode->user_id = $user->id;

        $qrcode->design = [
            'fillType' => 'solid'
        ];

        $qrcode->save();

        $qrcodeRedirect = QRCodeRedirect::where('qrcode_id', $qrcode->id)->first();

        $this->assertNotEmpty(
            $qrcodeRedirect
        );

        $this->useChromeUserAgent();

        for ($i = 0; $i < $paidPlan->number_of_scans; $i++) {
            $this
                ->get("/scan/$qrcodeRedirect->slug")
                ->assertStatus(302);
        }

        $response = $this
            ->get("/scan/$qrcodeRedirect->slug");

        $response->assertStatus(403);

        $response->assertSeeText('Scan limit reached.');
    }

    public function _test_paid_subscription()
    {
        list($user) = $this->newUserWithTestRole();

        $paidPlan = SubscriptionPlan::where('is_trial', false)->first();

        /** @var \App\Interfaces\SubscriptionManager */
        $subscriptionManager = app(SubscriptionManager::class);

        $subscriptionManager->saveSubscription([
            'subscription_plan_id' => $paidPlan->id,
            'user_id' => $user->id,
            'subscription_status' => SubscriptionStatus::STATUS_ACTIVE
        ]);

        $userSubscription = $user->subscriptions[0];

        $this->assertEquals(
            $paidPlan->id,
            $userSubscription->subscription_plan_id,
            'Paid subscription was not created for user.'
        );

        $userSubscription->statuses[0]->created_at = now()->subYears(2);

        $userSubscription->statuses[0]->save();

        /** @var \App\Jobs\MakeSubscriptionsExpired */
        $job = app(MakeSubscriptionsExpired::class);

        $job->handle();

        $userSubscription->refresh();

        $this->assertEquals(
            SubscriptionStatus::STATUS_EXPIRED,
            $userSubscription->statuses[0]->status,
            'Expired status was not assigned when paid subscription ended.'
        );

        $qrcode = new QRCode();

        $qrcode->type = 'url';

        $qrcode->data = [
            'url' => 'https://google.com'
        ];

        $qrcode->user_id = $user->id;

        $qrcode->design = [
            'fillType' => 'solid'
        ];

        $qrcode->save();

        $qrcodeRedirect = QRCodeRedirect::where('qrcode_id', $qrcode->id)->first();

        $this->assertNotEmpty(
            $qrcodeRedirect
        );

        $response = $this->get("/scan/$qrcodeRedirect->slug");

        $response->assertStatus(403);
    }

    public function _test_expired_trial_subscription()
    {
        list($user) = $this->newUserWithTestRole();

        $trialPlan = SubscriptionPlan::where('is_trial', true)->first();

        event(new Registered($user));

        $userSubscription = $user->subscriptions[0];

        $this->assertEquals($trialPlan->id, $userSubscription->subscription_plan_id, 'New user was not onboarded on trial plan');

        $userSubscription->statuses[0]->created_at = now()->subMonth();

        $userSubscription->statuses[0]->save();

        /** @var \App\Jobs\MakeSubscriptionsExpired */
        $job = app(MakeSubscriptionsExpired::class);

        $job->handle();

        $userSubscription->refresh();

        $this->assertEquals(SubscriptionStatus::STATUS_EXPIRED, $userSubscription->statuses[0]->status, 'Expired status was not assigned when trial subscription ended.');

        $qrcode = new QRCode();

        $qrcode->type = 'url';

        $qrcode->data = [
            'url' => 'https://google.com'
        ];

        $qrcode->user_id = $user->id;

        $qrcode->design = [
            'fillType' => 'solid'
        ];

        $qrcode->save();

        $qrcodeRedirect = QRCodeRedirect::where('qrcode_id', $qrcode->id)->first();

        $this->assertNotEmpty(
            $qrcodeRedirect
        );

        $response = $this->get("/scan/$qrcodeRedirect->slug");

        $response->assertStatus(403);
    }
}
