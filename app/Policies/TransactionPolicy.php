<?php

namespace App\Policies;

use App\Models\OfflinePaymentGatewayModel;
use App\Models\Transaction;
use App\Models\User;
use App\Support\PaymentProcessors\OfflinePaymentProcessor;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('transaction.list-all');
    }

    public function approve(User $user, Transaction $transaction)
    {
        return
            $this->isOfflinePayment($transaction) &&
            $user->permitted('transaction.approve');
    }

    public function reject(User $user, Transaction $transaction)
    {
        return
            $this->isOfflinePayment($transaction) &&
            $user->permitted('transaction.reject');
    }

    private function isOfflinePayment(Transaction $transaction)
    {
        return $transaction->source === (new OfflinePaymentProcessor)->slug();
    }
}
