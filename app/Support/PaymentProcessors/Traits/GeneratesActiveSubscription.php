<?php

namespace App\Support\PaymentProcessors\Traits;

use App\Interfaces\SubscriptionManager;
use App\Interfaces\UserManager;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait GeneratesActiveSubscription
{
    protected SubscriptionManager $subscriptionManager;

    private function randomPassword($shuffleRounds = 5)
    {
        $seed = array_merge(
            range('A', 'Z'),
            range('1', '9')
        );

        for ($i = 0; $i < $shuffleRounds; $i++) {
            shuffle($seed);
        }

        $passwordArray = array_slice($seed, 0, 8);

        return implode('', $passwordArray);
    }

    protected function generateUser($name, $email)
    {
        $user = User::whereEmail($email)->first();

        if ($user) {
            return compact('user');
        }

        $user = new User();

        $user->name = $name;

        $user->email = $email;

        $password = $this->randomPassword();

        $user->password = Hash::make($password);

        $user->save();

        $user->markEmailAsVerified();

        /** @var UserManager */
        $users = app(UserManager::class);

        $users->changeRole($user, Role::where('name', 'Client')->first());

        return compact('user', 'password');
    }

    protected function generateActiveSubscription(User $user, SubscriptionPlan $plan)
    {
        $subscription = $this->subscriptionManager->saveSubscription([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'subscription_status' => SubscriptionStatus::STATUS_PENDING_PAYMENT
        ]);

        $this->subscriptionManager->activateSubscription($subscription);

        return $subscription;
    }
}
