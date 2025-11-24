<?php

namespace App\Models;

use App\Interfaces\PaymentGateway as PaymentGatewayInterface;
use App\Repositories\PaypalPaymentGateway as RepositoriesPaypalPaymentGateway;
use App\Traits\HasPaymentGatewayFields;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class PaypalPaymentGateway extends PaymentGateway
{
    const SLUG = 'paypal';

    const MODE_SANDBOX = 'sandbox';

    const MODE_LIVE = 'live';

    const ENDPOINT_SANDBOX = 'https://api-m.sandbox.paypal.com/';

    const ENDPOINT_LIVE = 'https://api-m.paypal.com/';

    protected $table = 'payment_gateways';

    public $payment_fields = ['client_id', 'client_secret'];

    public static function getModes()
    {
        $ref = new \ReflectionClass(static::class);
        $constants = $ref->getConstants();

        return array_filter($constants, function ($val, $key) {
            return preg_match('/MODE/', $key);
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function setClientIdAttribute($value)
    {
        $this->setPaymentGatewayField('paypal_client_id', $value);
    }

    protected function getClientIdAttribute()
    {
        return $this->getPaymentGatewayField('paypal_client_id');
    }

    protected function setClientSecretAttribute($value)
    {
        $this->setPaymentGatewayField('paypal_client_secret', $value);
    }

    protected function getClientSecretAttribute()
    {
        return $this->getPaymentGatewayField('paypal_client_secret');
    }

    public static function instance()
    {
        $instance = self::where('slug', static::SLUG)->first();

        return $instance;
    }

    public static function isEnabled()
    {
        return self::instance()->enabled;
    }

    public static function getMode()
    {
        return self::instance()->mode;
    }

    public function resolveRepository(): ?PaymentGatewayInterface
    {
        return app(RepositoriesPaypalPaymentGateway::class);
    }
}
