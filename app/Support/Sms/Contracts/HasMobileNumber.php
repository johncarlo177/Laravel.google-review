<?php

namespace App\Support\Sms\Contracts;

interface HasMobileNumber
{
    public function getFormattedMobileNumber(): string;
}
