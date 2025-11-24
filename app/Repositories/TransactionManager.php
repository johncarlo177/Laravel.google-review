<?php

namespace App\Repositories;

use App\Interfaces\CurrencyManager;
use App\Interfaces\TransactionManager as TransactionManagerInterface;
use App\Models\File;
use App\Models\OfflinePaymentGatewayModel;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Support\PaymentProcessors\OfflinePaymentProcessor;
use App\Support\Webhooks\TransactionReceived;
use Exception;

class TransactionManager implements TransactionManagerInterface
{

    private CurrencyManager $currency;

    public function __construct()
    {
        $this->currency = app(CurrencyManager::class);
    }

    public function createTransaction(
        $subscription_id,
        $amount,
        $currency,
        $source,
        $status,
        $user_id = null,
        $description = null
    ): Transaction {
        // 
        $transaction = new Transaction();

        if (empty($subscription_id)) {
            if (empty($user_id)) {
                throw new Exception('Either user_id or subscription_id are required to create transaction record, you should provide one of them.');
            }

            if (empty($description)) {
                throw new Exception('Description is required because subscription_id is not provided');
            }
        } else {
            if (empty($user_id)) {
                $user_id = Subscription::find($subscription_id)->user_id;
            }

            if (empty($description)) {
                $description = t('Plan: ') . Subscription::find($subscription_id)->subscription_plan->name;
            }
        }

        $transaction->subscription_id = $subscription_id;
        $transaction->amount = $amount;
        $transaction->currency = $currency;
        $transaction->source = $source;
        $transaction->status = $status;
        $transaction->user_id = $user_id;
        $transaction->description = $description;

        $transaction->save();

        if ($status === Transaction::STATUS_SUCCESS) {
            TransactionReceived::withTransaction($transaction)->dispatch();
        }

        return $transaction;
    }

    public function approveOfflineTransaction(Transaction $transaction, User $user)
    {
        $transaction->status = Transaction::STATUS_SUCCESS;

        $transaction->approved_by_id = $user->id;

        $transaction->approved_at = time();

        $transaction->save();
    }

    public function rejectOfflineTransaction(Transaction $transaction, User $user)
    {
        $transaction->status = Transaction::STATUS_FAILED;

        $transaction->rejected_by_id = $user->id;

        $transaction->rejected_at = time();

        $transaction->save();
    }

    public function storeOfflineTransaction($data)
    {
        $transaction = new Transaction();

        $subscription = Subscription::findOrFail(
            $data['subscription_id']
        );

        $transaction->subscription_id = $subscription->id;
        $transaction->amount = $subscription->subscription_plan->price;
        $transaction->currency = $this->currency->enabledCurrency()->currency_code;
        $transaction->source = (new OfflinePaymentProcessor)->slug();
        $transaction->status = Transaction::STATUS_PENDING_CONFIRMATION;
        $transaction->description = t('Offline payment');
        $transaction->save();

        $file = File::find($data['payment_proof_id']);

        $file->attachable_id = $transaction->id;

        $file->save();

        $transaction->refresh();

        return $transaction;
    }
}
