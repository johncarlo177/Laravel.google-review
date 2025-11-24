<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as Base;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends Base
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(t('Verify Email Address'))
            ->line(t('Please click the button below to verify your email address.'))
            ->action(t('Verify Email Address'), $url)
            ->line(t('If you did not create an account, no further action is required.'));
    }
}
