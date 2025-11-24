<?php

namespace App\Interfaces;

use App\Models\Transaction;

use App\Models\User;

interface TransactionManager
{
    public function approveOfflineTransaction(Transaction $transaction, User $user);

    public function rejectOfflineTransaction(Transaction $transaction, User $user);

    public function storeOfflineTransaction($data);

    public function createTransaction(
        $subscription_id,
        $amount,
        $currency,
        $source,
        $status,
        $user_id = null,
        $description = null
    ): Transaction;
}
