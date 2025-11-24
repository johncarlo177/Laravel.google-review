<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSubscriptionPlanRequest;
use Illuminate\Http\Request;

use App\Interfaces\ModelIndex;
use App\Models\SubscriptionPlan;
use App\Plugins\PluginManager;
use App\Support\SubscriptionPlansManager;


class SubscriptionPlanController extends Controller
{
    private SubscriptionPlansManager $subscriptionPlansManager;

    public function __construct()
    {
        $this->subscriptionPlansManager = new SubscriptionPlansManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, ModelIndex $index)
    {
        if ($request->boolean('all') || $request->boolean('list_all')) {
            return SubscriptionPlan::get();
        }

        return $index
            ->build(new SubscriptionPlan(), $request)
            ->withQuery(function ($query) use ($request) {
                if ($request->has('is_hidden')) {
                    $query->where('is_hidden', $request->boolean('is_hidden'));
                }
            })
            ->list();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\SaveSubscriptionPlanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SaveSubscriptionPlanRequest $request)
    {
        $model = new SubscriptionPlan($request->all());

        PluginManager::doAction(PluginManager::ACTION_SUBSCRIPTION_PLAN_BEFORE_SAVE, $model, $request);

        $model->save();

        dispatch(function () {
            HomePageController::rebuildHomePageCache();
        })->afterResponse();

        return $model;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SubscriptionPlan  $subscriptionPlan
     * @return \Illuminate\Http\Response
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return $subscriptionPlan;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\SaveSubscriptionPlanRequest  $request
     * @param  \App\Models\SubscriptionPlan  $subscriptionPlan
     * @return \Illuminate\Http\Response
     */
    public function update(SaveSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->fill($request->all());

        PluginManager::doAction(PluginManager::ACTION_SUBSCRIPTION_PLAN_BEFORE_SAVE, $subscriptionPlan, $request);

        $subscriptionPlan->save();

        dispatch(function () {
            HomePageController::rebuildHomePageCache();
        })->afterResponse();

        return $subscriptionPlan;
    }

    public function duplicate(SubscriptionPlan $subscriptionPlan)
    {
        return $this->subscriptionPlansManager->duplicate($subscriptionPlan);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SubscriptionPlan  $subscriptionPlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        if ($subscriptionPlan->subscriptions()->count() > 0) {
            return [
                'error_message' => t('Cannot delete plan with associated subscriptions, you can hide it instead')
            ];
        }

        $subscriptionPlan->delete();

        dispatch(function () {
            HomePageController::rebuildHomePageCache();
        })->afterResponse();

        return $subscriptionPlan;
    }
}
