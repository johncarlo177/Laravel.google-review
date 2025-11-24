<?php

namespace App\Support\Sms\Contracts;

interface SendsSms
{
    public function toSms($notifiable): string;
}
