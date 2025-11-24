<?php

namespace App\Interfaces;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionStatus;
use App\Models\User;
use Illuminate\Support\Carbon;

interface SubscriptionManager
{
    public function setExpiredSubscriptions($subscriptions = null);

    public function saveSubscription($data);

    public function activateSubscription(Subscription $subscription, $forceAssignNewStatus = false);

    public function createSubscription(
        User $user,
        SubscriptionPlan $plan,
        $status = SubscriptionStatus::STATUS_PENDING_PAYMENT
    );

    public function deactivateSubscription(Subscription $subscrption);

    public function userHasActiveSubscription(User $user);

    public function userDynamicQRCodesLimitReached(User $user, $type = null);

    public function userScanLimitReached(User $user);

    public function userInvitedUsersLimitReached(User $user);

    public function subscriptionIsExpired(Subscription $subscription);

    public function getSubscriptionAgeInDays(Subscription $subscription);

    public function getSubscriptionRemainingDays(Subscription $subscription);

    public function userDomainsLimitReached(User $user);

    public function userAiGenerationsLimitReached(User $user);

    public function userOnTrialPlan(User $user): bool;

    public function shouldEnforceSubscriptionRules(User $user = null);

    public function userHasAccessToSubscriptionPlanFeature(User $user, string $feature);

    public function cancelSubscription(Subscription $subscription);

    /**
     * @return Carbon
     */
    public function calculateExpiresAt(Subscription $subscription, $fallbackActivationDate = null);

    public function deletePendingSubscriptions();

    public function remainingDynamicQRCodes(User $user, $type = null);

    public function allowedToCreateDynamicQRCodes(User $user, $count = 1, $type = null);
}
