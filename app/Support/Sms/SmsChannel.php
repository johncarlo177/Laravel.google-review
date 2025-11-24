<?php

namespace App\Support\Sms;

use App\Support\Sms\Contracts\HasMobileNumber;
use App\Support\Sms\Contracts\SendsSms;
use App\Support\Sms\Drivers\Manager;
use Illuminate\Notifications\Notification;
use InvalidArgumentException;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send(HasMobileNumber $notifiable, Notification $notification)
    {
        if (!$notification instanceof SendsSms) {
            throw new InvalidArgumentException("Notification must implement " . SendsSms::class);
        }

        $message = $notification->toSms($notifiable);

        if (empty($message)) return;

        $number = $notifiable->getFormattedMobileNumber();

        if (empty($number)) return;

        $smsManager = new Manager;

        $driver = $smsManager->enabledDriver();

        if (!$driver) {
            return;
        }

        return $driver->send($number, $message);
    }
}
