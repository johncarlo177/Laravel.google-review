<?php

namespace App\Notifications\Dynamic;

use Carbon\Carbon;
use App\Models\User;
use App\Notifications\Dynamic\Interfaces\ShouldBroadcast;

class DynamicQRCodeLimitReached extends Base implements ShouldBroadcast
{
    public function slug()
    {
        return 'dynamic-qrcode-limit-reached';
    }

    public function shouldBroadcast(User $user): bool
    {
        if (!$this->subscriptions->userHasActiveSubscription($user))
            return false;

        if (!$this->subscriptions->userDynamicQRCodesLimitReached($user))
            return false;

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
