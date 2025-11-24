<?php

namespace App\Support\Invoicing;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Models\Transaction;

class InvoiceManager
{
    protected Transaction $transaction;

    public static function withTransaction(Transaction $transaction)
    {
        $instance = new static;

        $instance->transaction = $transaction;

        return $instance;
    }

    protected function isBillingCollectionEnabled()
    {
        return config('billing_collection_enabled') === 'enabled';
    }

    public function generateInvoice()
    {
        if (!$this->isBillingCollectionEnabled()) {
            return;
        }

        if ($this->transaction->status != Transaction::STATUS_SUCCESS) {
            return;
        }

        $invoice = new Invoice();

        $invoice->user_id = $this->transaction->user_id;

        $invoice->status = Invoice::STATUS_PAID;

        $invoice->billing_details_response_id = $this->transaction->subscription->billing_details_custom_form_response_id;

        // invoice is saved automatically in addItem method.
        $invoice->addItem(
            $this->subscriptionItem()
        );

        return $invoice;
    }

    protected function subscriptionItem()
    {
        return SubscriptionItem::withSubscription($this->transaction->subscription)
            ->withAmount($this->transaction->amount)
            ->get();
    }
}
