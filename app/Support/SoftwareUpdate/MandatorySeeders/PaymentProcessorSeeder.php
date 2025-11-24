<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Support\PaymentProcessors\AlipayChinaPaymentProcessor;
use App\Support\PaymentProcessors\DinteroPaymentProcessor;
use App\Support\PaymentProcessors\MercadoPagoPaymentProcessor;
use App\Support\PaymentProcessors\MolliePaymentProcessor;
use App\Support\PaymentProcessors\OfflinePaymentProcessor;
use App\Support\PaymentProcessors\PaddlePaymentProcessor;
use App\Support\PaymentProcessors\PayFastPaymentProcessor;
use App\Support\PaymentProcessors\PayKickstartPaymentProcessor;
use App\Support\PaymentProcessors\PaymentProcessor;
use App\Support\PaymentProcessors\PayPalPaymentProcessor;
use App\Support\PaymentProcessors\PayStackPaymentProcessor;
use App\Support\PaymentProcessors\PayTrPaymentProcessor;
use App\Support\PaymentProcessors\PayULatamPaymentProcessor;
use App\Support\PaymentProcessors\RazorpayPaymentProcessor;
use App\Support\PaymentProcessors\StripePaymentProcessor;
use App\Support\PaymentProcessors\TwoCheckoutPaymentProcessor;
use App\Support\PaymentProcessors\XenditPaymentProcessor;
use App\Support\PaymentProcessors\YooKassaPaymentProcessor;

class PaymentProcessorSeeder extends Seeder
{
    /** @var string software_version/seeder_version */
    protected $version = 'v2.25.3/1';

    protected function run()
    {
        $this->seedPayPalProcessor();

        $this->seedStripeProcessor();

        $this->seedPaddleProcessor();

        $this->seedRazorPayProcessor();

        $this->seedMercadoPagoProcessor();

        $this->seedPayTrProcessor();

        $this->seedPayFastProcessor();

        $this->seedDefault(XenditPaymentProcessor::class);

        $this->seedDefault(MolliePaymentProcessor::class);

        $this->seedDefault(PayStackPaymentProcessor::class);

        $this->seedDefault(AlipayChinaPaymentProcessor::class);

        $this->seedDefault(YooKassaPaymentProcessor::class);

        $this->seedPayKickstartProcessor();

        $this->seedDefault(PayULatamPaymentProcessor::class);

        $this->seedDefault(TwoCheckoutPaymentProcessor::class);

        $this->seedDefault(DinteroPaymentProcessor::class);

        $this->seedOfflinePaymentProcessor();
    }

    private function setConfig(PaymentProcessor $processor, $key, $value)
    {
        if (empty($processor->config($key)))
            $processor->setConfig($key, $value);
    }

    private function seedDefault($processorClass)
    {
        $processor = new $processorClass();

        $this->setConfig($processor, 'display_name', t('Credit Card'));

        $this->setConfig($processor, 'pay_button_text', t('Pay Now'));
    }

    private function seedPayKickstartProcessor()
    {
        $this->seedDefault(PayKickstartPaymentProcessor::class);

        $processor = new PayKickstartPaymentProcessor;

        $this->setConfig($processor, 'email_template', 'Hello FULL_NAME,

Thank you for subscribing to PLAN_NAME plan. Here is your login credentials:
    
Email: EMAIL

Password: PASSWORD');


        $this->setConfig($processor, 'upgrade_email_template', 'Hello FULL_NAME,

Thank you for subscribing to PLAN_NAME plan. Your account has been upgraded and your login credentials remain the same.');
    }

    private function seedPayFastProcessor()
    {
        $processor = new PayFastPaymentProcessor();

        $this->setConfig($processor, 'display_name', t('Credit Card'));

        $this->setConfig($processor, 'pay_button_text', t('Pay Now'));
    }

    private function seedOfflinePaymentProcessor()
    {
        $processor = new OfflinePaymentProcessor;

        $this->setConfig($processor, 'display_name', t('Bank Transfer'));

        $this->setConfig($processor, 'pay_button_text', t('Attach Transfer Note'));

        $this->setConfig($processor, 'customer_instructions', 'Please pay us with swift transfer, and attach the transfer note below.

Our bank details are:

Bank name: **ACME BANK**

IBAN number: **120901930193**

Account name: **COMPANY NAME INC.**
        ');
    }

    private function seedRazorPayProcessor()
    {
        $processor = new RazorpayPaymentProcessor();

        $this->setConfig($processor, 'display_name', 'Credit Card');

        $this->setConfig($processor, 'pay_button_text', 'अब भुगतान करें');
    }

    private function seedPayTrProcessor()
    {
        $processor = new PayTrPaymentProcessor();

        $this->setConfig($processor, 'display_name', 'Credit Card');

        $this->setConfig($processor, 'pay_button_text', 'Şimdi öde');
    }

    private function seedMercadoPagoProcessor()
    {
        $processor = new MercadoPagoPaymentProcessor();

        $this->setConfig($processor, 'display_name', 'Mercado Pago');

        $this->setConfig($processor, 'pay_button_text', 'Pagar');
    }

    private function seedPayPalProcessor()
    {
        $paypal = new PayPalPaymentProcessor();

        $this->setConfig($paypal, 'display_name', t('PayPal'));

        $this->setConfig($paypal, 'pay_button_text', t('Pay with PayPal'));
    }

    private function seedStripeProcessor()
    {
        $stripe = new StripePaymentProcessor();

        $this->setConfig($stripe, 'display_name', t('Credit Card'));
        $this->setConfig($stripe, 'pay_button_text', t('Pay Now'));
    }

    private function seedPaddleProcessor()
    {
        $paddle = new PaddlePaymentProcessor;

        $this->setConfig($paddle, 'mode', 'sandbox');

        $this->setConfig($paddle, 'display_name', t('Credit Card'));

        $this->setConfig($paddle, 'pay_button_text', t('Pay Now'));
    }
}
