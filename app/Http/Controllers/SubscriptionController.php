<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;

use App\Interfaces\ModelIndex;
use App\Interfaces\SubscriptionManager;
use App\Models\Subscription;
use App\Models\SubscriptionStatus;
use App\Models\User;

use Illuminate\Http\Request;


class SubscriptionController extends Controller
{
    private SubscriptionManager $subscriptions;

    public function __construct(SubscriptionManager $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ModelIndex $index, Request $request)
    {
        return $index
            ->build(new Subscription(), $request)
            ->withQuery(function ($query) {
                $query->with(
                    'user',
                    'subscription_plan',
                    'statuses',
                    'billing_details',
                    'billing_details.custom_form'
                );
            })
            ->list(
                [
                    'user.name',
                    'user.email',
                    'subscription_plan.name',
                    'statuses.status',
                ]
            );
    }




    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSubscriptionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSubscriptionRequest $request)
    {
        return new SubscriptionResource(
            $this->subscriptions->saveSubscription($request->all())
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function show(Subscription $subscription)
    {
        return new SubscriptionResource($subscription);
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        $canUpdateAny = $request->user()->permitted('subscription.update-any');

        if (!$canUpdateAny) {
            abort(403);
        }

        return new SubscriptionResource(
            $this->subscriptions->saveSubscription($request->all())
        );
    }

    public function listStatuses()
    {
        return SubscriptionStatus::getStatuses();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return $subscription;
    }

    public function deletePendingSubscriptions(Request $request)
    {
        /**
         * @var User
         */
        $user = $request->user();

        if (!$user->isSuperAdmin()) return;

        return [
            'count' => $this->subscriptions->deletePendingSubscriptions()
        ];
    }
}
