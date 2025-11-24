<?php

namespace App\Http\Controllers;

use App\Events\OfflineTransactionApproved;
use App\Events\OfflineTransactionReceived;
use App\Events\OfflineTransactionRejected;
use App\Interfaces\FileManager;
use App\Interfaces\ModelIndex;
use App\Interfaces\SubscriptionManager;
use App\Interfaces\TransactionManager;
use App\Models\File;
use App\Models\OfflinePaymentGatewayModel;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{

    private $transactions;
    private $subscriptions;

    public function __construct(TransactionManager $transactions, SubscriptionManager $subscriptions)
    {
        $this->transactions = $transactions;

        $this->subscriptions = $subscriptions;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ModelIndex $modelIndex, Request $request)
    {
        return $modelIndex->build(new Transaction, $request)

            ->withQuery(function ($query) {
                $query->with(
                    'subscription',
                    'subscription.user',
                    'user',
                    'subscription.subscription_plan',
                    'payment_proof'
                );
            })

            ->list([
                'amount',
                'currency',
                'status',
                'subscription.user.name',
                'subscription.subscription_plan.name',
            ]);
    }

    public function uploadProofOfPayment(Request $request, FileManager $files)
    {
        $request->merge([
            'attachable_type' => Transaction::class,
            'attachable_id' => null,
            'type' => FileManager::FILE_TYPE_TRANSACTION_PROOF_OF_PAYMENT
        ]);

        $result = $files->store($request);

        return $result;
    }



    public function storeOfflineTransaction(Request $request)
    {
        $transaction =  $this->transactions->storeOfflineTransaction($request->all());

        event(new OfflineTransactionReceived($transaction));

        return $transaction;
    }

    public function approveOfflineTransaction(Transaction $transaction, Request $request)
    {
        $this->transactions->approveOfflineTransaction($transaction, $request->user());

        $this->subscriptions->activateSubscription($transaction->subscription);

        event(new OfflineTransactionApproved($transaction));

        return [
            'success' => true
        ];
    }

    public function rejectOfflineTransaction(Transaction $transaction, Request $request)
    {
        $this->transactions->rejectOfflineTransaction($transaction, $request->user());

        $this->subscriptions->deactivateSubscription($transaction->subscription);

        event(new OfflineTransactionRejected($transaction));

        return [
            'success' => true
        ];
    }
}
