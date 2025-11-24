<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as Base;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Base
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(t('Reset Password Notification'))
            ->line(t('You are receiving this email because we received a password reset request for your account.'))
            ->action(t('Reset Password'), $url)
            ->line(
                sprintf(
                    t('This password reset link will expire in %s minutes.'),
                    config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')
                )
            )
            ->line(t('If you did not request a password reset, no further action is required.'));
    }
}
