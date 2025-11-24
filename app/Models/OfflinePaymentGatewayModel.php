<?php

namespace App\Models;

use App\Interfaces\PaymentGateway as PaymentGatewayInterface;
use Illuminate\Support\Str;

class OfflinePaymentGatewayModel extends PaymentGateway
{
    const SLUG = 'offline-payment-gateway';

    protected $table = 'payment_gateways';

    public $payment_fields = ['customer_instructions'];

    public $appends = ['customer_instructions_html'];

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
        return null;
    }

    public function getRedirectUrlAttribute()
    {
        return $this->getPaymentGatewayField('redirect_url');
    }

    public function setRedirectUrlAttribute($value)
    {
        $this->setPaymentGatewayField('redirect_url', $value);
    }

    public function getCustomerInstructionsAttribute()
    {
        return $this->getPaymentGatewayField('customer_instructions');
    }

    public function setCustomerInstructionsAttribute($value)
    {
        return $this->setPaymentGatewayField('customer_instructions', $value);
    }

    public function getCustomerInstructionsHtmlAttribute()
    {
        return Str::markdown($this->customer_instructions ?? '');
    }
}
