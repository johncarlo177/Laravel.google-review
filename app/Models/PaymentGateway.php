<?php

namespace App\Models;

use App\Events\PaymentGatewaySaved;
use App\Exceptions\NotImplementedException;
use App\Interfaces\PaymentGateway as InterfacesPaymentGateway;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'enabled', 'mode'];

    protected $casts = [
        'payment_gateway_fields' => 'array',
        'enabled' => 'boolean',
    ];

    protected $dispatchesEvents = [
        'saved' => PaymentGatewaySaved::class
    ];

    protected $payment_fields = [];

    public function __construct()
    {
        $this->fillable = array_merge(
            $this->fillable,
            $this->payment_fields
        );
    }

    public function make(): PaymentGateway
    {
        $class = $this->getPaymentGatewayClasses()
            ->first(fn ($class) => $class::SLUG === $this->slug);

        return $class::instance();
    }

    private function getPaymentGatewayClasses()
    {
        $files = glob(__DIR__ . '/*.php');

        $classes = collect($files)
            ->filter(fn ($file) => preg_match('/paymentgateway/i', $file))
            ->map(fn ($file) => str_replace('.php', '', basename($file)))
            ->filter(fn ($class) => $class !== $this->getBaseClassName())
            ->map(fn ($class) => __NAMESPACE__ . '\\' . $class);

        return $classes;
    }

    private function getBaseClassName()
    {
        return str_replace(__NAMESPACE__ . '\\', '', __CLASS__);
    }

    protected function paymentGatewayFields(): Attribute
    {
        return new Attribute(
            function ($value) {
                return $value === null ? [] : json_decode($value, true);
            },
            fn ($value) => empty($value) ? [] : json_encode($value)
        );
    }

    protected function getPaymentGatewayField($name)
    {
        return $this->payment_gateway_fields[$name] ?? null;
    }

    protected function setPaymentGatewayField($name, $value)
    {
        $this->payment_gateway_fields = array_merge(
            $this->payment_gateway_fields,
            [$name => $value]
        );
    }

    public function resolveRepository(): ?InterfacesPaymentGateway
    {
        throw new NotImplementedException();
    }
}
