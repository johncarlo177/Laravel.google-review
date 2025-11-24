<?php

namespace App\Support\PaymentProcessors\Interfaces;


interface HasCustomThankYouPage
{
    public function shouldRenderCustomThankYouPage(): bool;
}
