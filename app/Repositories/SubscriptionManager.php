<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Config;
use App\Models\Subscription;
use App\Interfaces\UserManager;
use App\Events\SubscriptionCanceled;
use App\Events\SubscriptionActivated;
use App\Interfaces\SubscriptionManager as SubscriptionManagerInterface;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionStatus;
use App\Policies\QRCodePolicy;
use App\Support\AI\UsageManager;
use App\Support\Billing\BillingManager;
use App\Support\DomainManager;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use App\Support\QRCodeManager;
use App\Support\QRCodeScanManager;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use Carbon\Carbon;
use InvalidArgumentException;

class SubscriptionManager implements SubscriptionManagerInterface
{
    use WriteLogs;

    private QRCodeTypeManager $qrcodeTypes;
    private QRCodeManager $qrcodes;
    private UserManager $users;

    private BillingManager $billing;

    public function __construct()
    {
        $this->qrcodeTypes = new QRCodeTypeManager;
        $this->qrcodes = new QRCodeManager;
        $this->users = app(UserManager::class);

        $this->billing = new BillingManager();
    }

    public function setExpiredSubscriptions($subscriptions = null)
    {
        if (!$subscriptions)
            $subscriptions = Subscription::with('statuses', 'subscription_plan')->get();


        /** @var Subscription */
        foreach ($subscriptions as $subscription) {

            $subscription->load('statuses');

            $status = $subscription->statuses->first();

            if (!$status) {
                continue;
            }

            if ($status->status === SubscriptionStatus::STATUS_EXPIRED) {
                continue;
            }

            if ($status->status === SubscriptionStatus::STATUS_ACTIVE) {

                if (!$subscription->expires_at) {
                    return;
                }

                if (now()->isAfter($subscription->expires_at)) {
                    // Add expired status to this subscription
                    $this->assignExpiredStatus($subscription);
                }
            }
        }
    }

    public function getSubscriptionRemainingDays(Subscription $subscription)
    {
        if (
            !$this->shouldEnforceSubscriptionRules($subscription->user)
        ) return null;

        $days = max(
            0,
            $this->getExpirationDate($subscription)->diffInDays(now(), true)
        );

        return $days;
    }

    /** @deprecated */
    public function getSubscriptionAgeInDays(Subscription $subscription)
    {
        if (
            !$this->shouldEnforceSubscriptionRules($subscription->user)
        ) return null;

        if (
            $subscription->statuses[0]->status ===
            SubscriptionStatus::STATUS_EXPIRED
        ) return 0;

        $days = $subscription->expires_at?->diffInDays(Carbon::now());

        if ($days === null) {
            return null;
        }

        // One day grace period
        return $days + 1;
    }

    private function getExpirationDate(Subscription $subscription)
    {
        if ($subscription->expires_at) return $subscription->expires_at;

        return $this->calculateExpiresAt($subscription);
    }

    public function subscriptionIsExpired(Subscription $subscription)
    {
        if (!$this->shouldEnforceSubscriptionRules($subscription->user)) return false;

        return $this->getExpirationDate($subscription)->isBefore(
            now()->subDay()
        );
    }

    /**
     * @return Carbon
     */
    public function calculateExpiresAt(
        Subscription $subscription,
        $fallbackActivationDate = null
    ) {
        /**
         * @var SubscriptionStatus
         */
        $activeStatus = $subscription->statuses->first(
            fn($s) => $s->status === SubscriptionStatus::STATUS_ACTIVE
        );

        $rawDate = $activeStatus?->created_at ?? $fallbackActivationDate;

        if (!$rawDate) return null;

        $date = new Carbon($rawDate);

        $expiresAt = $date->addDays(
            intval($subscription->subscription_plan->exiprationDays())
        );

        return $expiresAt;
    }

    public function activateSubscription(
        Subscription $subscription,
        $forceAssignNewStatus = false
    ) {
        $activeStatus = SubscriptionStatus::STATUS_ACTIVE;

        $this->assignStatus($subscription, $activeStatus);

        $subscription->expires_at = $this->calculateExpiresAt($subscription);

        $subscription->save();

        event(new SubscriptionActivated($subscription));
    }

    public function deactivateSubscription(Subscription $subscrption)
    {
        if ($subscrption->statuses[0]->status != SubscriptionStatus::STATUS_ACTIVE) {
            return;
        }

        $subscrption->statuses[0]->delete();
    }

    private function assignExpiredStatus($subscription)
    {
        $this->assignStatus($subscription, SubscriptionStatus::STATUS_EXPIRED);
    }

    public function assignStatus($subscription, string $status)
    {
        $found = array_filter(
            SubscriptionStatus::getStatuses(),
            fn($s) => $s === $status
        );

        if (empty($found)) {
            throw new InvalidArgumentException("Status ($status) is invalid, expected on of: " . implode(', ', SubscriptionStatus::getStatuses()));
        }

        $model = new SubscriptionStatus();

        $model->status = $status;

        $model->subscription_id = $subscription->id;

        $model->created_at = now();

        $model->save();

        $subscription->refresh();
    }

    public function createSubscription(
        User $user,
        SubscriptionPlan $plan,
        $status = SubscriptionStatus::STATUS_PENDING_PAYMENT
    ) {
        $subscription = new Subscription();

        $subscription->subscription_plan_id = $plan->id;

        $subscription->user_id = $user->id;

        $subscription->save();

        $this->assignStatus($subscription, $status);

        return $subscription;
    }

    public function saveSubscription($data)
    {
        $subscription = new Subscription();

        if (isset($data['id'])) {
            $subscription = Subscription::findOrFail($data['id']);
        }

        $subscription->fill($data);

        $subscription->save();

        if ($subscription->statuses->first()?->status !== $data['subscription_status']) {
            $this->assignStatus($subscription, $data['subscription_status']);
        }

        $subscription->refresh();

        return $subscription;
    }

    public function userHasActiveSubscription(User $user)
    {
        if (QRCodePolicy::canListAll($user)) return true;

        $subscription = $this->users->getCurrentSubscription($user);

        if (!$subscription) return false;

        if ($subscription->statuses->isEmpty()) return false;

        $currentStatus = $subscription->statuses[0]->status;

        if ($currentStatus === SubscriptionStatus::STATUS_EXPIRED) {
            return false;
        }

        if ($currentStatus === SubscriptionStatus::STATUS_CANCELED) {
            return $this->getSubscriptionRemainingDays($subscription) > 0;
        }

        return $currentStatus === SubscriptionStatus::STATUS_ACTIVE;
    }

    public function userOnTrialPlan(User $user): bool
    {
        $subscription = $this->users->getCurrentSubscription($user);

        if (!$subscription) return false;

        if ($user->isSuperAdmin()) return false;

        return $subscription->subscription_plan->is_trial;
    }

    public function remainingDynamicQRCodes(User $user, $type = null)
    {
        if (!$this->shouldEnforceSubscriptionRules($user))
            return PHP_INT_MAX;

        $dynamicTypes = $this->qrcodeTypes->dynamicSlugs();

        if (!$type) {
            $type = $dynamicTypes->all();
        }

        $count = $this->qrcodes->getQRCodeCount(
            $user,
            null,
            $type
        );

        $plan = $this->users->getCurrentPlan($user);

        if (
            $this->isTotalNumberUnlimited(
                $plan->getDynamicTypeLimits($type)
            )
        ) {
            return PHP_INT_MAX;
        }

        return $plan->getDynamicTypeLimits($type) - $count;
    }

    public function allowedToCreateDynamicQRCodes(
        User $user,
        $count = 1,
        $type = null
    ) {
        if ($user->permitted('qrcode.list-all')) return true;

        if (empty($this->users->getCurrentSubscription($user))) {
            return false;
        }

        return $this->remainingDynamicQRCodes($user, $type) >= $count &&
            !$this->subscriptionIsExpired(
                $this->users->getCurrentSubscription($user)
            );
    }

    public function userDynamicQRCodesLimitReached(User $user, $type = null)
    {
        if (!$this->shouldEnforceSubscriptionRules($user)) return false;



        return $this->remainingDynamicQRCodes($user, $type) <= 0;
    }


    public function userScanLimitReached(User $user)
    {
        if (!$this->shouldEnforceSubscriptionRules($user)) return false;

        $scans = new QRCodeScanManager();

        $plan = $this->users->getCurrentPlan($user);

        if (!$plan) {
            return false;
        }

        if (
            $this->isTotalNumberUnlimited(
                $plan->number_of_scans
            )
        ) {

            return false;
        }

        return $scans->getScansByUser($user) >= $plan->number_of_scans;
    }

    public function userDomainsLimitReached(User $user)
    {
        if (!$this->shouldEnforceSubscriptionRules($user)) return false;

        $plan = $this->users->getCurrentPlan($user);

        if (!$plan) return false;

        if (
            $this->isTotalNumberUnlimited(
                $plan->number_of_custom_domains
            )
        ) {
            return false;
        }

        $domainManager = new DomainManager();

        return $domainManager->getPublishedDomainsOfUser($user)->count()
            >= $plan->number_of_custom_domains;
    }

    public function userInvitedUsersLimitReached(User $user)
    {
        if (!$this->shouldEnforceSubscriptionRules($user)) return false;

        $plan = $this->users->getCurrentPlan($user);

        $this->logDebug(
            'Number of users = %s, is unlimited %s',
            $plan->number_of_users,
            $this->isTotalNumberUnlimited(
                $plan->number_of_users
            ) ? 'true' : 'false'
        );

        if (
            $this->isTotalNumberUnlimited(
                $plan->number_of_users
            )
        ) {
            return false;
        }

        return $user->sub_users()->count() >= $plan->number_of_users;
    }

    public function userAiGenerationsLimitReached(User $user)
    {
        if (!$this->shouldEnforceSubscriptionRules($user)) return false;

        /**
         * @var \App\Models\SubscriptionPlan
         */
        $plan = $this->users->getCurrentPlan($user);

        if (
            $this->isTotalNumberUnlimited(
                $plan->number_of_ai_generations
            )
        ) {
            return false;
        }

        return UsageManager::forUser($user)->getUsage() >= $plan->number_of_ai_generations;
    }

    public function shouldEnforceSubscriptionRules(User $user = null)
    {
        if ($user && QRCodePolicy::canListAll($user)) return false;

        if (config('app.wplus_integration_enabled')) return false;

        if (
            Config::get('app.paid_subscriptions') && Config::get('app.paid_subscriptions') == 'disabled'
        ) return false;


        if ($this->billing->isAccountCreditBilling()) return false;

        if (!$user->isClient()) {
            return false;
        }

        return true;
    }

    public function userHasAccessToSubscriptionPlanFeature(User $user, string $feature)
    {
        if (!$this->shouldEnforceSubscriptionRules($user)) return true;

        $plan = $this->users->getCurrentPlan($user);

        return collect($plan->features)->filter(
            fn($f) => preg_match("/$feature/", $f)
        )->count() > 0;
    }

    public function isTotalNumberUnlimited($value)
    {
        return $value == -1;
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $status = SubscriptionStatus::STATUS_CANCELED;

        $this->assignStatus($subscription, $status);

        $subscription->refresh();

        event(new SubscriptionCanceled($subscription));

        $processors = new PaymentProcessorManager;

        $processors->cancelSubscriptionForAllProcessors($subscription);
    }

    public function deletePendingSubscriptions()
    {
        $subscriptions = Subscription::with('statuses')->whereHas('statuses', function ($query) {
            $query->where('status', SubscriptionStatus::STATUS_PENDING_PAYMENT);
        })->get();

        $subscriptions = $subscriptions->filter(function (Subscription $subscription) {
            return $subscription->statuses->first()->status
                === SubscriptionStatus::STATUS_PENDING_PAYMENT;
        });

        $subscriptions->each(function (Subscription $subscription) {
            $subscription->delete();
        });

        return $subscriptions->count();
    }
}
