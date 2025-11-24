<?php

namespace App\Support\QRCodeTypes\ViewComposers\Components\WhatsAppOrder;

use App\Models\QRCode;
use App\Repositories\UserManager;
use App\Support\System\MemoryCache;
use App\Support\System\Traits\WriteLogs;

class BuyButton
{
    use WriteLogs;

    protected static $didRenderJsVars = false;
    protected QRCode $qrcode;
    protected $item;

    public static function withQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public function item($item)
    {
        $this->item = $item;

        return $this;
    }

    protected function isEnabled()
    {
        return $this->isFeatureAllowed() &&
            $this->isConfigEnabled() &&
            !empty($this->getMobileNumber());
    }

    protected function isFeatureAllowed()
    {
        $user = $this->qrcode->user;

        if ($user->isSuperAdmin()) {
            return true;
        }

        $users = new UserManager;

        $subscription = $users->getCurrentSubscription($user);

        $plan = $subscription->subscription_plan;

        return collect($plan->features)
            ->filter(
                fn($f) => $f === 'whatsapp-order'
            )
            ->isNotEmpty();
    }

    public function getMobileNumber()
    {
        return $this->field('whatsapp_order_mobile_number');
    }

    protected function isConfigEnabled()
    {
        $value = $this->field('whatsapp_order_enabled');

        return $value === 'enabled';
    }

    protected function getDesignArray()
    {
        return MemoryCache::remember(__METHOD__ . $this->qrcode->id, function () {
            return $this->qrcode->getWebPageDesign()?->design;
        });
    }

    public function shouldRenderForm()
    {
        return !$this::$didRenderJsVars;
    }

    public function field($key, $default = null)
    {
        $array = $this->getDesignArray();

        return @$array[$key] ?? $default;
    }

    protected function getOrderType()
    {
        $value = $this->field('whatsapp_order_order_type');

        return empty($value) ? 'delivery' : $value;
    }

    protected function getCurrentOrderNumber()
    {
        $this->logDebug(
            'whatsapp_order_number = %s',
            $this->field('whatsapp_order_number')
        );

        $this->logDebug(
            'current design array = %s',
            $this->getDesignArray(),
        );

        $value = intval($this->field('whatsapp_order_number'));

        $this->logDebug(
            'value = %s',
            $value,
        );

        return max($value, 1);
    }

    protected function getTableNumber()
    {
        $number = $this->field('whatsapp_order_table_number', '');

        return request()->input('table-number', $number);
    }

    public function renderJsVars()
    {
        if ($this::$didRenderJsVars) {
            return '';
        }

        $this::$didRenderJsVars = true;

        $vars = [
            'mobile_number' => $this->getMobileNumber(),
            'payment_url' => $this->getPaymentUrl(),
            'order_type' => $this->getOrderType(),
            'table_number' => $this->getTableNumber(),
            'whatsapp_order_terms_conditions' => $this->field('whatsapp_order_terms_conditions', ''),
            'open_cart_text' => t('Open Cart'),
            'order_number' => $this->getCurrentOrderNumber(),
        ];

        return collect(array_keys($vars))->map(function ($name) use ($vars) {
            return sprintf('<script>__$$$whatsapp_order$$$%s__ = "%s"; </script>', $name, $vars[$name]);
        })->join("\n");
    }

    protected function hasValidCurrency()
    {
        $price = @$this->item['price'];

        $currency = str_replace('/-', '', $price);

        $currency = preg_replace('/[0-9\.]/', '', $currency);

        return !preg_match('#/#', $currency);
    }

    protected function isNumericPrice($value)
    {
        $price = preg_replace('/[^0-9\.]/', '', $value);

        return $price !== '' && is_numeric($price);
    }

    protected function hasVariations()
    {
        $this->logDebug('variations = %s', @$this->item['variations']);

        return is_array(@$this->item['variations']) && !empty(@$this->item['variations']);
    }

    protected function hasValidPrice()
    {
        $price = @$this->item['price'];

        return $this->isNumericPrice($price) || $this->hasVariations();
    }

    protected function getPaymentQRCodeId()
    {
        $paymentType = $this->field('whatsapp_order_payment_option');

        if ($paymentType === 'paypal') {
            return $this->field('whatsapp_paypal_payment_qrcode_id');
        }

        return $this->field('whatsapp_upi_payment_qrcode_id');
    }

    public function getPaymentUrl()
    {
        $qrcodeId = $this->getPaymentQRCodeId();

        if (!$qrcodeId) {
            return '';
        }

        $qrcode = QRCode::find($qrcodeId);

        if (!$qrcode) {
            return '';
        }

        return $qrcode?->redirect?->route;
    }

    public function render()
    {
        $this->logDebug('From render');

        if (!$this->isEnabled()) {
            $this->logDebug('is not enabled');
            return;
        }

        $this->logDebug('is enabled');

        if (!$this->hasValidPrice()) {
            $this->logDebug('doesnt have valid price');
            return;
        }

        if (!$this->hasValidCurrency()) {
            return;
        }

        return view(
            'qrcode.components.whatsapp-order.buy-button',
            [
                'item' => $this->item,
                'button' => $this,
            ]
        );
    }

    public function renderConfigs()
    {
        $this->logDebug('Rendering configs .... ');

        $this->logDebug('Current order number %s', $this->getCurrentOrderNumber());

        $form = view('qrcode.components.whatsapp-order.form');

        return implode(' ', [
            $form,
            $this->renderJsVars()
        ]);
    }
}
