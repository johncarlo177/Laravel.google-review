<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * @group tested
 */
class SubscriptionControllerTest extends TestCase
{
    public function test_subscription_list()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this
            ->actingAs($user)
            ->get('/api/subscriptions');

        $response->assertStatus(403);

        $this->addPerm($role, 'subscription.list-all');

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/subscriptions');

        $response->assertStatus(200);

        $response->assertJsonPath('total', Subscription::count());
    }

    public function test_store_subscription()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this->actingAs($user)->post('/api/subscriptions');

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('subscription.store'));

        $user->refresh();

        $response = $this->actingAs($user)->post('/api/subscriptions');

        $response->assertStatus(422);
    }

    public function test_show_subscription()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subscription = $this->makeSubscription($user);

        $response = $this->actingAs($user)->get('/api/subscriptions/' . $subscription->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('subscription.show'));

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/subscriptions/' . $subscription->id);

        $response->assertStatus(200);

        $subscription = Subscription::where('user_id', '<>', $user->id)->first();

        $response = $this->actingAs($user)->get('/api/subscriptions/' . $subscription->id);

        $response->assertStatus(403);
    }

    private function makeSubscription($user)
    {
        $subscription = new Subscription([
            'subscription_plan_id' => SubscriptionPlan::all()->random()->id,
            'user_id' => $user->id
        ]);

        $subscription->save();

        return $subscription;
    }
}
