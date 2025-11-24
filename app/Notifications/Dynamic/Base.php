<?php

namespace App\Notifications\Dynamic;

use App\Interfaces\SubscriptionManager;
use App\Interfaces\UserManager;
use App\Models\Config;
use App\Models\User;
use App\Notifications\Dynamic\Interfaces\ShouldBroadcast;
use App\Support\Sms\Contracts\SendsSms;
use App\Support\Sms\SmsChannel;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\SUpport\Str;
use Throwable;

abstract class Base extends Notification implements ShouldQueue, SendsSms
{
    use Queueable;
    use WriteLogs;

    protected SubscriptionManager $subscriptions;

    protected UserManager $users;

    protected $notifiable;

    public function __construct()
    {
        $this->subscriptions = app(SubscriptionManager::class);
        $this->users = app(UserManager::class);
    }

    public abstract function slug();

    public function send(User $user)
    {
        if (!$this->enabled()) {
            return;
        }

        $user->notify($this);
    }

    public function dynamicBroadcast()
    {
        if (!($this instanceof ShouldBroadcast)) {
            return;
        }

        if (!$this->enabled()) return;

        $users = User::all();

        foreach ($users as $user) {
            try {
                if (!$this->shouldBroadcast($user)) {
                    continue;
                }

                $this->beforeNotify($user);

                $user->notify($this);

                $this->setLastSent($user);

                $this->afterNotify($user);
            } catch (Throwable $th) {

                if (!app()->environment('demo')) {
                    $this->logWarning(
                        'Error while sending notification. %s',
                        $th->getMessage(),
                        @$th->getTrace()[0] . "\n" . @$th->getTrace()[1]
                    );
                }
            }
        }
    }

    protected function beforeNotify(User $user)
    {
        $this->logInfo('Sending to ' . $user->email);
    }

    protected function afterNotify(User $user) {}

    protected function setUserMeta(User $user, $key, $value)
    {
        return $user->setMeta($this->userMetaKey($key), $value);
    }

    protected function getUserMeta(User $user, $key)
    {
        return $user->getMeta($this->userMetaKey($key));
    }

    protected function setLastSent(User $user)
    {
        return $this->setUserMeta($user, 'last-sent', now()->timestamp);
    }

    protected function getLastSent(User $user)
    {
        return $this->getUserMeta($user, 'last-sent');
    }

    protected function userMetaKey($key)
    {
        return $this->configKey($key);
    }

    public function via()
    {
        if (!$this->enabled()) {
            return [];
        }

        $channels = [];

        if ($this->smsChannelEnabled()) {
            $channels[] = SmsChannel::class;
        }

        if ($this->emailChannelEnabled()) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    protected function smsChannelEnabled()
    {
        return true;
    }

    protected function emailChannelEnabled()
    {
        return true;
    }

    protected function getEmailSubject()
    {
        return $this->evaluatedConfig('email_subject');
    }

    public function toMail($notifiable)
    {
        $this->notifiable = $notifiable;

        $message = (new MailMessage)
            ->subject($this->getEmailSubject());

        $body = $this->evaluatedConfig('email_body');

        $body = Str::markdown($body);

        $message->line(new HtmlString($body));

        $message->greeting = '';

        return $message;
    }

    public function toSms($notifiable): string
    {
        $this->notifiable = $notifiable;

        return $this->evaluatedConfig('sms_body');
    }

    protected function evaluatedConfig($key)
    {
        return $this->evaluateVariables(
            $this->config($key)
        );
    }

    protected function evaluateVariables($text)
    {
        $vars = $this->configVariables();

        foreach ($vars as $key => $value) {
            $text = str_replace($key, $value, $text);
        }

        return $text;
    }

    protected function configVariables()
    {
        return [
            'PLANS_PAGE_URL' => $this->pricingPlanUrl(),
        ];
    }

    private function pricingPlanUrl()
    {
        $url = config('app.frontend_pricing_plans_url');

        if (empty($url)) {
            $url = url('/pricing-plans');
        }

        return $url;
    }

    public function enabled()
    {
        return $this->config('enabled');
    }

    public function config($key)
    {
        if (is_array($key)) {
            $_key = array_keys($key)[0];
            $_value = $key[$_key];

            return $this->setConfig($_key, $_value);
        }

        return $this->getConfig($key);
    }

    private function getConfig($key)
    {
        return Config::get($this->configKey($key));
    }

    private function setConfig($key, $value)
    {
        return Config::set($this->configKey($key), $value);
    }

    private function configKey($key)
    {
        return sprintf('notifications.%s.%s', $this->slug(), $key);
    }

    public function defaultEmailSubject() {}

    public function defaultEmailBody() {}

    public function defaultSmsBody() {}
}
