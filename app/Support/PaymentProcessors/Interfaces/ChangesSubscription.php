<?php

namespace App\Support\PaymentProcessors\Interfaces;

use App\Models\SubscriptionPlan;
use App\Models\User;

interface ChangesSubscription
{
    public function canChangeSubscription(User $user);

    public function generateChangeSubscriptionLink(
        User $user,
        SubscriptionPlan $toPlan,
        ?callable $onSuccess = null,
        ?callable $onFail = null,
    );

    public static function changeSubscriptionResultPageRoute();

    public function renderChangeSubscriptionResultPage();
}
