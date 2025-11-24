<?php

namespace App\Support\AI;

use App\Models\SubscriptionPlan;
use App\Console\Kernel;
use App\Interfaces\UserManager;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Console\Scheduling\Schedule;
use \Illuminate\Console\Scheduling\CallbackEvent;
use Throwable;

class UsageManager
{
    use WriteLogs;

    private User $user;

    private UserManager $users;

    public static function boot()
    {
        if (!config('app.installed')) return;

        try {
            static::bootSchedule();
        } catch (Throwable $th) {
            static::logWarning('Error booting schedule.');
        }
    }

    private static function bootSchedule()
    {
        static::scheduleFrequencyReset(
            SubscriptionPlan::FREQUENCY_MONTHLY,
            function (CallbackEvent $event) {
                $event->monthlyOn(1);
            }
        );

        static::scheduleFrequencyReset(
            SubscriptionPlan::FREQUENCY_YEARLY,
            function (CallbackEvent $event) {
                $event->monthlyOn(1);
            }
        );
    }

    private static function scheduleFrequencyReset($frequency, $callback)
    {
        Kernel::addSchedule(
            function (Schedule $schedule) use ($frequency, $callback) {
                $schedule->call(function () use ($frequency) {

                    try {

                        static::resetUsageForFrequency($frequency);
                        //
                    } catch (Throwable $th) {

                        (new static)->logWarningf($th->getMessage());
                        //
                    }
                });

                $callback($schedule);
            }
        );
    }

    public static function forUser(User $user)
    {
        $instance = new static;

        /**
         * @var UserManager
         */
        $users = app(UserManager::class);

        $instance->user = $users->getParentUser($user);

        return $instance;
    }

    public static function resetUsageForFrequency($usageFrequency)
    {
        $users = static::getAllUsers();

        foreach ($users as $user) {

            $instance = static::forUser($user);

            $subscription = $instance->users->getCurrentSubscription($user);

            $userFrequency = $subscription->subscription_plan->frequency;

            if ($userFrequency === $usageFrequency) {
                $instance->resetUsage();
            }
        }
    }

    private static function getAllUsers()
    {
        return (new User)->newModelQuery()->select('id')->get();
    }

    public function __construct()
    {
        $this->users = app(UserManager::class);
    }

    private function resetUsage()
    {
        $this->setUsage(0);

        $this->logInfo('Monthly usage has been reset for user ' . $this->user->email);
    }

    public function increaseUsage()
    {
        $value = $this->getUsage();

        $this->setUsage($value + 1);
    }

    public function getUsage()
    {
        return $this->user->getMeta($this->usageKey()) ?? 0;
    }

    private function setUsage($value)
    {
        $this->user->setMeta($this->usageKey(), $value);
    }

    private function usageKey()
    {
        return static::class . '::aiUsage';
    }
}
