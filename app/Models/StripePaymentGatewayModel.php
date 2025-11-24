<?php

namespace App\Models;

use App\Interfaces\PaymentGateway as PaymentGatewayInterface;
use App\Repositories\StripePaymentGateway;


class StripePaymentGatewayModel extends PaymentGateway
{
    const SLUG = 'stripe';

    protected $table = 'payment_gateways';

    public $payment_fields = ['publisher_key', 'secret_key'];

    protected function getPublisherKeyAttribute()
    {
        return $this->getPaymentGatewayField('stripe_publisher_key');
    }

    protected function setPublisherKeyAttribute($value)
    {
        $this->setPaymentGatewayField('stripe_publisher_key', $value);
    }

    protected function getSecretKeyAttribute()
    {
        return $this->getPaymentGatewayField('stripe_secret_key');
    }

    protected function setSecretKeyAttribute($value)
    {
        $this->setPaymentGatewayField('stripe_secret_key', $value);
    }

    public static function instance()
    {
        $instance = static::where('slug', static::SLUG)->first();

        return $instance;
    }

    public static function isEnabled()
    {
        return self::instance()->enabled;
    }

    public function resolveRepository(): ?PaymentGatewayInterface
    {
        return app(StripePaymentGateway::class);
    }
}
