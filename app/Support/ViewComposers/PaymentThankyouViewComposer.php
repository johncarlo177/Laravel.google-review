<?php

namespace App\Support\ViewComposers;

use App\Support\PaymentProcessors\PaymentProcessor;
use App\Support\PaymentProcessors\PaymentProcessorManager;


class PaymentThankyouViewComposer extends BaseComposer
{
    private ?PaymentProcessor $processor;

    public function __construct()
    {
        $manager = new PaymentProcessorManager();

        $this->processor = $manager->find(request()->processor);
    }

    public static function path(): string
    {
        return 'payment.thankyou';
    }

    public function thankYouViewPath()
    {
        return $this->processor?->thankYouViewPath();
    }
}
