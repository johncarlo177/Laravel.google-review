<?php

namespace App\Support\Invoicing;

use App\Models\CustomForm;
use App\Models\CustomFormResponse;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Support\PaymentProcessors\StripePaymentProcessor;
use App\Support\System\Traits\HasClassSettings;

class DataMock
{
    use HasClassSettings;

    public function mock()
    {
        Invoice::get()->each(function (Invoice $invoice) {
            $invoice->delete();
        });

        collect(range(1, 10))->each(function () {

            /**
             * @var User
             */
            $user = User::find(1);

            $subscription = $this->getUserSubscription($user);

            $this->makeBillingFormResponse($subscription);

            $processor = new StripePaymentProcessor();

            $processor->createTransaction(
                remote_transaction_id: time() . '',
                subscription_id: $subscription->id,
                amount: $subscription->subscription_plan->price,
                currency: 'USD',
                status: Transaction::STATUS_SUCCESS,
                user_id: $user->id,
                description: $subscription->subscription_plan->description
            );
        });
    }

    protected function makeBillingFormResponse(Subscription $subscription)
    {
        $form = CustomForm::find(1);

        $response = new CustomFormResponse();

        $response->fields = collect($form->fields)->map(function ($field) {
            return [
                'id' => $field['id'],
                'name' => $field['name'],
                'value' => 'Value Of ' . $field['name']
            ];
        });

        $response->custom_form_id = $form->id;

        $response->user_id = $subscription->user_id;

        $response->save();

        $subscription->billing_details_custom_form_response_id = $response->id;

        $subscription->save();
    }

    protected function getUserSubscription(User $user)
    {
        $subscription = @$user->subscriptions[0];

        if (!$subscription) {
            // 
            $subscription = new Subscription();

            $subscription->user_id = $user->id;

            $subscription->subscription_plan_id = SubscriptionPlan::orderByDesc('price')->first()->id;

            $subscription->save();
        }

        return $subscription;
    }
}
