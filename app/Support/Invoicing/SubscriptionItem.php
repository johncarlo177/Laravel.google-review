<?php

namespace App\Support\Invoicing;

use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Repositories\SubscriptionManager;

class SubscriptionItem
{
    protected Subscription $subscription;

    protected $amount;

    public static function withSubscription(Subscription $subscription)
    {
        $instance = new static;

        $instance->subscription = $subscription;

        return $instance;
    }

    public function withAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function get()
    {
        $item = new InvoiceItem();

        $item->name = t('Subscription') . ' ' . $this->getSubscriptionPeriod();

        $item->description = $this->subscription->subscription_plan->description;

        $item->quantity = 1;

        $item->unit_price = $this->amount;

        $item->save();

        return $item;
    }

    protected function getSubscriptionPeriod()
    {
        $from = $this->subscription->updated_at->format('Y.m.d');

        $manager = new SubscriptionManager;

        $to = $manager->calculateExpiresAt(
            $this->subscription,
            $this->subscription->updated_at
        )->format('Y.m.d');

        return "$from - $to";
    }
}
