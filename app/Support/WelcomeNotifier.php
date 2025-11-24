<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\Dynamic\WelcomeAdmin;
use App\Notifications\Dynamic\WelcomeCustomer;

class WelcomeNotifier
{
    protected const EVENT_TYPE_REGISTRATION = 'event:registration';
    protected const EVENT_TYPE_OTP_VERIFICATION = 'event:otp-verification';
    protected const EVENT_TYPE_OTP_REGISTRATION = 'event:otp-registration';

    protected User $user;
    protected $eventType = null;

    public static function withUser(User $user)
    {
        $instance = new static;

        $instance->user = $user;

        return $instance;
    }

    public function onRegistration()
    {
        $this->eventType = $this::EVENT_TYPE_REGISTRATION;

        return $this;
    }

    public function onOtpVerification()
    {
        $this->eventType = $this::EVENT_TYPE_OTP_VERIFICATION;

        return $this;
    }

    public function onOtpRegistration()
    {
        $this->eventType = $this::EVENT_TYPE_OTP_REGISTRATION;

        return $this;
    }

    protected function isVerificationEnabled()
    {
        return config('app.email_verification_after_sign_up') !== 'disabled';
    }

    protected function isVerificationDisabled()
    {
        return !$this->isVerificationEnabled();
    }

    public function notifyIfNeeded()
    {
        if ($this->eventType === $this::EVENT_TYPE_REGISTRATION) {
            if ($this->isVerificationDisabled()) {
                return $this->notify();
            }
        }

        if ($this->eventType === $this::EVENT_TYPE_OTP_VERIFICATION) {
            return $this->notify();
        }

        if ($this->eventType === $this::EVENT_TYPE_OTP_REGISTRATION) {
            return $this->notify();
        }
    }

    protected function notify()
    {
        WelcomeCustomer::withCustomer($this->user)
            ->notify();

        WelcomeAdmin::withCustomer($this->user)
            ->notify();
    }
}
