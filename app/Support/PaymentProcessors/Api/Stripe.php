<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use Stripe\StripeClient;

use Exception;

class Stripe
{
    use WriteLogs;

    private StripeClient $stripe;

    private bool $taxEnabled;

    private $taxBehavior = 'inclusive';

    public function __construct($secret_key, $taxEnabled, $taxBehavior)
    {
        $this->taxEnabled = $taxEnabled;

        try {
            $this->stripe = new StripeClient($secret_key);
        } catch (Exception $ex) {
            $this->logWarning($ex->getMessage());
        }
    }

    public function createProduct($name, $description)
    {
        $data = compact('name', 'description');

        if ($this->taxEnabled) {
            $data['tax_code'] = $this->taxCode();
        }

        return $this->stripe->products->create($data);
    }

    private function taxCode()
    {
        // SaaS category
        return 'txcd_10103000';
    }

    public function createOneTimePrice($amount, $currency, $product_id)
    {
        $data = [
            'active' => true,
            'unit_amount' => $amount * 100, // cents
            'currency' => $currency,
            'product' => $product_id
        ];

        if ($this->taxEnabled) {
            $data['tax_behavior'] = $this->taxBehavior();
        }

        return $this->stripe->prices->create($data);
    }

    public function createRecurringPrice(
        $interval,
        $amount,
        $currency,
        $product_id
    ) {
        $data = [
            'active' => true,
            'unit_amount' => $amount * 100, // cents
            'recurring' => [
                'interval' => $interval,
                'interval_count' => 1,
            ],
            'currency' => $currency,
            'product' => $product_id
        ];

        if ($this->taxEnabled) {
            $data['tax_behavior'] = $this->taxBehavior();
        }

        return $this->stripe->prices->create($data);
    }

    private function taxBehavior()
    {
        // available options are inclusive | exclusive
        return $this->taxBehavior;
    }

    public function getSubscription($subscription_id)
    {
        return $this->stripe->subscriptions->retrieve($subscription_id);
    }

    public function changeSubscription($subscription_id, $price_id)
    {
        $subscription = $this->getSubscription($subscription_id);

        $this->logDebugf('Updating subscription %s', json_encode($subscription, JSON_PRETTY_PRINT));

        $this->stripe->subscriptions->update(
            $subscription->id,
            [
                'cancel_at_period_end' => false,
                'proration_behavior' => 'create_prorations',
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $price_id,
                    ],
                ],
            ]
        );

        $subscription = $this->getSubscription($subscription_id);

        $this->logDebugf('Update completed %s', json_encode($subscription, JSON_PRETTY_PRINT));

        return $subscription;
    }

    public function registerWebhook($url)
    {
        return $this->stripe->webhookEndpoints->create([
            'url' => $url,
            'enabled_events' => [
                'payment_intent.succeeded',
                'payment_intent.payment_failed'
            ],
        ]);
    }

    public function listWebhooks()
    {
        return $this->stripe->webhookEndpoints->all()->data;
    }

    public function clearWebhooks()
    {
        $webhooks = $this->listWebhooks();

        foreach ($webhooks as $webhook) {
            $this->stripe->webhookEndpoints->delete($webhook->id);
        }
    }

    public function createCheckoutSession(
        $success_url,
        $cancel_url,
        $client_reference_id,
        $line_items,
        $email,
        $payment_intent_metadata = [],
        $mode = 'payment',
    ) {
        $return_url = $success_url . (strpos($success_url, '?') ? '&' : '?') . 's_id={CHECKOUT_SESSION_ID}';

        $data = [
            'success_url' => $return_url,
            'cancel_url' => $cancel_url,
            'mode' => $mode,
            'client_reference_id' => $client_reference_id,
            'line_items' => $line_items,
            'customer_email' => $email,
        ];

        if ($mode === 'subscription') {
            $data = array_merge($data, [
                'subscription_data' => [
                    'metadata' => $payment_intent_metadata,
                ],
            ]);
        } else {
            // for one time payments
            $data = array_merge($data, [
                'payment_intent_data' => [
                    'metadata' => $payment_intent_metadata,
                ],
            ]);
        }

        if ($this->taxEnabled) {
            $data['automatic_tax'] = [
                'enabled' => true
            ];
        }

        $session = $this->stripe->checkout->sessions->create($data);

        return $session;
    }

    public function generateOneTimePayLink(
        $success_url,
        $cancel_url,
        $local_subscription_id,
        $stripe_price_id,
        $email
    ) {
        $return_url = $success_url . (strpos($success_url, '?') ? '&' : '?') . 's_id={CHECKOUT_SESSION_ID}';

        $data = [
            'success_url' => $return_url,
            'cancel_url' => $cancel_url,
            'mode' => 'payment',
            'client_reference_id' => $local_subscription_id,
            'line_items' => [
                [
                    'price' => $stripe_price_id,
                    'quantity' => 1,
                ]
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'subscription_id' => $local_subscription_id,
                ],
            ],
            'customer_email' => $email,
        ];

        if ($this->taxEnabled) {
            $data['automatic_tax'] = [
                'enabled' => true
            ];
        }

        $session = $this->stripe->checkout->sessions->create($data);

        return $session->url;
    }

    public function generateSubscribePayLink(
        $success_url,
        $cancel_url,
        $local_subscription_id,
        $stripe_price_id,
        $email,
        $extra = []
    ) {
        $return_url = $success_url . (strpos($success_url, '?') ? '&' : '?') . 's_id={CHECKOUT_SESSION_ID}';

        $data = [
            'success_url' => $return_url,
            'cancel_url' => $cancel_url,
            'mode' => 'subscription',
            'client_reference_id' => $local_subscription_id,
            'line_items' => [
                [
                    'price' => $stripe_price_id,
                    'quantity' => 1,
                ]
            ],
            'customer_email' => $email,
            'subscription_data' => [
                'metadata' => array_merge(
                    [
                        'subscription_id' => $local_subscription_id,
                    ],
                    $extra
                )
            ]
        ];

        if ($this->taxEnabled) {
            $data['automatic_tax'] = [
                'enabled' => true
            ];
        }

        $session = $this->stripe->checkout->sessions->create($data);

        return $session->url;
    }

    public function getCheckoutSessionByPaymentIntent($id)
    {
        $data = $this->stripe->checkout->sessions->all([
            'limit' => 1,
            'payment_intent' => $id
        ])->data;

        $session = @$data[0];

        return $this->getCheckoutSession($session?->id);
    }

    public function getSubscriptionByInvoice($invoiceId)
    {
        $invoice = $this->stripe->invoices->retrieve($invoiceId);

        $subscriptionId = $invoice->lines->data[0]?->subscription;

        return $this->stripe->subscriptions->retrieve($subscriptionId);
    }

    public function getCheckoutSession($id)
    {
        if (!$id) return null;

        return $this->stripe->checkout->sessions->retrieve($id, [
            'expand' => ['subscription']
        ]);
    }

    public function cancelSubscription($id)
    {
        if (!$id) return;

        return $this->stripe->subscriptions->cancel($id);
    }
}
