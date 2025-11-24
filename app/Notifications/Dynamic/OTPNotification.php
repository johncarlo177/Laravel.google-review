<?php

namespace App\Notifications\Dynamic;

use App\Models\User;

class OTPNotification extends Base
{
    protected User $user;

    protected $otp = null;


    public static function withUser(User $user)
    {
        $instance = new static;

        $instance->user = $user;

        return $instance;
    }

    public function withOTP($otp)
    {
        $this->otp = $otp;

        return $this;
    }

    public function slug()
    {
        return 'otp';
    }

    protected function configVariables()
    {
        return [
            'OTP_CODE' => $this->otp,
            'ACCOUNT_OWNER' => $this->user->name,
            'APP_NAME' => config('app.name'),
            'EMAIL_ADDRESS' => $this->user->email,
        ];
    }

    public function defaultEmailSubject()
    {
        return 'OTP Verification - OTP_CODE';
    }



    public function defaultEmailBody()
    {
        return <<<END_TEMPLATE
# Hello ACCOUNT_OWNER,

Your OTP code is OTP_CODE.

Thank you.


END_TEMPLATE;
    }

    public function defaultSmsBody()
    {
        return <<<TEMPLATE

Your OTP code is OTP_CODE.


TEMPLATE;
    }
}
