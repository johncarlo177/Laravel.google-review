<?php

namespace App\Support\Auth\Otp;

use App\Models\OtpVerification;
use App\Models\User;
use App\Notifications\Dynamic\OTPNotification;
use App\Support\StringHelper;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class EmailVerifier
{
    use WriteLogs;

    protected $email = null;

    protected $otp = null;

    public static function withEmail($email)
    {
        $instance = new static;

        $instance->email = $email;

        return $instance;
    }

    public function withOtp($otp)
    {
        $this->otp = $otp;

        return $this;
    }

    protected function isVerified()
    {
        if (app()->environment('local')) {
            return true;
        }

        /**
         * @var OtpVerification
         */
        $model = OtpVerification::where('otp', $this->otp)
            ->where('email', $this->email)
            ->whereNull('verified_at')
            ->first();

        if (!$model) {
            return false;
        }

        $model->verified_at = now();

        $model->save();

        return true;
    }


    public function verify()
    {
        $verified = $this->isVerified();

        if ($verified) {
            $this->markEmailAsVerifiedIfNeeded();
        }

        return $verified;
    }

    protected function markEmailAsVerifiedIfNeeded()
    {
        /**
         * @var User
         */
        $user = User::where('email', $this->email)->first();

        if (!$user) {
            return;
        }

        $user->markEmailAsVerified();
    }

    public function isRecentlyVerified()
    {
        if (app()->environment('local')) {
            return true;
        }

        /**
         * @var OtpVerification
         */
        $model = OtpVerification::where(
            'email',
            $this->email
        )
            ->whereNotNull('verified_at')
            ->orderBy('verified_at', 'desc')
            ->first();

        if (!$model) {
            return false;
        }

        return $model->verified_at->isAfter(now()->subMinutes(30));
    }

    public function send()
    {
        $otp = $this->generateRandomOtp();

        $model = new OtpVerification();

        $model->email = $this->email;

        $model->otp = $otp;

        $model->save();

        $this->sendVerificationEmail($model);

        return $model;
    }

    private function generateRandomOtp()
    {
        return StringHelper::random(5, '123456789');
    }

    private function sendVerificationEmail(OtpVerification $model)
    {
        if (app()->environment('local')) {
            $this->logDebug('OTP verification is %s', $model->otp);
        }

        /**
         * @var User
         */
        $user = User::whereEmail($model->email)->first();

        if (!$user) {
            $user = new User;

            $user->email = $this->email;
        }

        $user->notifyNow(
            OTPNotification::withUser($user)
                ->withOTP($model->otp)
        );
    }
}
