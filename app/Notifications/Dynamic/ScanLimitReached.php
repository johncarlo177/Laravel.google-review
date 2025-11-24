<?php

namespace App\Notifications\Dynamic;

use App\Models\User;
use App\Notifications\Dynamic\Interfaces\ShouldBroadcast;
use Carbon\Carbon;

class ScanLimitReached extends Base implements ShouldBroadcast
{
    public function slug()
    {
        return 'scan-limit-reached';
    }

    public function shouldBroadcast(User $user): bool
    {
        if (!$this->subscriptions->userHasActiveSubscription($user)) return false;

        if (!$this->subscriptions->userScanLimitReached($user)) return false;

        if (!$this->getLastSent($user)) return true;

        $date = new Carbon($this->getLastSent($user));

        if (abs($date->diffInDays(now())) > $this->remindDays()) {
            return true;
        }

        return false;
    }

    // Remind every days
    private function remindDays()
    {
        return 30;
    }
}
