<?php

namespace App\Jobs;

use App\Interfaces\UserManager;
use App\Models\User;

class TrialUsersCleaner
{
    protected UserManager $users;

    public function __construct()
    {
        $this->users = app(UserManager::class);
    }

    protected function shouldRun()
    {
        return !is_nan(+$this->getDaysToKeepTrialUsers()) && $this->getDaysToKeepTrialUsers() > 0;
    }

    protected function getDaysToKeepTrialUsers()
    {
        return config('authentication.keep_trial_users_for');
    }

    public function handle()
    {
        if (!$this->shouldRun()) {
            return;
        }

        $users = User::all();

        $users->each(
            function (User $user) {

                if (!$user->isClient()) {
                    return;
                }

                $subscription = $this->users->getCurrentSubscription($user);

                if (!$subscription?->subscription_plan?->is_trial) {
                    return;
                }

                if (
                    $user->created_at->diffInDays(
                        now(),
                        true
                    ) > $this->getDaysToKeepTrialUsers()
                ) {
                    $this->users->deleteUser($user);
                }
            }
        );
    }
}
