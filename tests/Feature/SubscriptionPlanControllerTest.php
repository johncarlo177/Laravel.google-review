<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;

/**
 * @group tested
 */
class SubscriptionPlanControllerTest extends TestCase
{
    public function test_plan_list()
    {
        // listing plans is allowed for guests now.
        // there is no need to test permissions.
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this
            ->actingAs($user)
            ->get('/api/subscription-plans');


        $response->assertStatus(200);
    }

    public function test_store_plan()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this->actingAs($user)->post('/api/subscription-plans');

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('subscription-plan.store'));

        $user->refresh();

        $response = $this->actingAs($user)->post('/api/subscription-plans');

        $response->assertStatus(422);
    }

    public function test_update_subscription_plan()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subject = SubscriptionPlan::all()->first(fn ($p) => $p->monthly_price > 0);

        $response = $this->actingAs($user)->put('/api/subscription-plans/' . $subject->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('subscription-plan.update-any'));

        $user->refresh();

        $response = $this->actingAs($user)->put('/api/subscription-plans/' . $subject->id);

        $response->assertStatus(422);

        $subject->name = 'test';

        $data = json_decode(json_encode($subject), true);

        $response = $this->actingAs($user)->put(
            '/api/subscription-plans/' . $subject->id,
            $data
        );

        $response->assertStatus(200);

        $response->assertJsonPath('name', 'test');
    }

    public function test_show_subscription_plan()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subject = SubscriptionPlan::all()->random();

        $response = $this->actingAs($user)->get('/api/subscription-plans/' . $subject->id);

        $response->assertStatus(200);

        // show plan is allowed for guests.
    }

    public function test_destroy_subscription_plan()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subscriptionPlan = SubscriptionPlan::factory()->pro()->state(
            [
                'name' => 'test'
            ]
        )->create();

        $response = $this->actingAs($user)->delete('/api/subscription-plans/' . $subscriptionPlan->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('subscription-plan.destroy-any'));

        $user->refresh();

        $response = $this->actingAs($user)->delete('/api/subscription-plans/' . $subscriptionPlan->id);

        $response->assertStatus(200);
    }
}
