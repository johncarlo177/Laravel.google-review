<?php

namespace App\Support\PaymentProcessors\Interfaces;

interface RegistersWebhook
{
    public function registerWebhook(): bool;
}
