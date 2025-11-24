<?php

namespace App\Support\Webhooks;

use App\Models\Transaction;

class TransactionReceived extends BaseDispatcher
{
    protected Transaction $transaction;

    protected function event()
    {
        return 'transaction_received';
    }

    public static function withTransaction(Transaction $transaction)
    {
        $instance = new static;

        $instance->transaction = $transaction;

        return $instance;
    }

    protected function getPayloadType()
    {
        return 'Transaction';
    }

    protected function getPayload()
    {
        $this->transaction->load('user');

        $array = $this->transaction->toArray();

        unset($array['user']['roles']);

        return $array;
    }
}
