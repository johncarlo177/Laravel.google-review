<?php

namespace App\Support\PaymentProcessors\Traits;

use App\Interfaces\SubscriptionManager;
use App\Interfaces\UserManager;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Throwable;

trait HandleChangeSubscription
{
    use WriteLogs;

    protected UserManager $users;

    protected SubscriptionManager $subscriptionManager;

    public abstract function slug();

    protected abstract function changeSubscription(
        $remoteId,
        SubscriptionPlan $toPlan
    );

    public function canChangeSubscription(User $user)
    {
        $remoteSubscriptionId = $this->getRemoteSubscriptionId($user);

        $hasActiveSubscription = $this
            ->subscriptionManager
            ->userHasActiveSubscription($user);


        $this->logDebugf('remote subscription id for user (%s) is %s', $user->email, $remoteSubscriptionId);

        return  $hasActiveSubscription && !empty($remoteSubscriptionId);
    }

    public function generateChangeSubscriptionLink(
        User $user,
        SubscriptionPlan $toPlan,
        ?callable $onSuccess = null,
        ?callable $onFail = null
    ) {
        try {
            $this->changeSubscription(
                $this->getRemoteSubscriptionId($user),
                $toPlan
            );

            if (is_callable($onSuccess))
                call_user_func($onSuccess);

            return $this->changeSubscriptionSuccessUrl();
        } catch (Throwable $th) {
            $this->logErrorf('Cannot change subscription %s' . $th->getMessage());

            if (is_callable($onFail))
                call_user_func($onFail);

            return $this->changeSubscriptionFailUrl();
        }
    }

    public static function changeSubscriptionResultPageRoute()
    {
        $instance = new static;

        return sprintf('/payment/processor/%s/change-subscription-result', $instance->slug());
    }

    public function renderChangeSubscriptionResultPage()
    {
        if (!$this->validateSignedUrl()) {
            return redirect()->route('payment.invalid');
        }

        $result = request()->input('result');

        return view('payment.change-plan-result', [
            'result' => $result
        ]);
    }

    private function signUrl($url)
    {
        $signature = $this->generateSignature($url);

        return sprintf('%s&signature=%s', $url, $signature);
    }

    private function validateSignedUrl()
    {
        $fullUrl = request()->url() . '?' . $_SERVER['QUERY_STRING'];

        $this->logDebugf('Validating url %s', $fullUrl);

        $urlWithoutSignature = request()->url() . '?result=' . request()->input('result');

        $this->logDebugf('Url without signature %s', $urlWithoutSignature);

        $signed = $this->signUrl($urlWithoutSignature);

        $this->logDebugf('Signed URL %s', $signed);

        return  $signed === $fullUrl;
    }

    private function generateSignature($string)
    {
        return hash_hmac('sha256', $string, config('app.key'));
    }

    private function singedResultUrl($result)
    {
        $route = $this::changeSubscriptionResultPageRoute();

        $this->logDebugf('Generating signed URL for route %s', $route);

        $url = url(
            $route . '?result='  . $result
        );

        $this->logDebugf('Url to sign %s', $url);

        $signedUrl = $this->signUrl($url);

        $this->logDebugf('Signed URL %s', $signedUrl);

        return $signedUrl;
    }

    private function changeSubscriptionFailUrl()
    {
        return $this->singedResultUrl('fail');
    }

    private function changeSubscriptionSuccessUrl()
    {
        return $this->singedResultUrl('success');
    }

    protected function setRemoteSubscriptionId(User $user, $remoteId)
    {
        $this->logDebugf(
            'Setting remote subscription id for user (%s) subscription id = %s',
            $user->email,
            $remoteId
        );

        $user->setMeta(
            $this->_getRemoteSubscriptionIdMetaKey(),
            $remoteId
        );
    }

    protected function getRemoteSubscriptionId(User $user)
    {
        return $user->getMeta($this->_getRemoteSubscriptionIdMetaKey());
    }

    private function _getRemoteSubscriptionIdMetaKey()
    {
        return sprintf('%s_subscription_id', $this->slug());
    }
}
