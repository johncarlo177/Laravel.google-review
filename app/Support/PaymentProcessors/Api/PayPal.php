<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPal
{
    use WriteLogs;

    private const ENDPOINT_SANDBOX = 'https://api-m.sandbox.paypal.com/';

    private const ENDPOINT_LIVE = 'https://api-m.paypal.com/';

    private ?string $mode;

    private string $endpoint;

    private ?string $clientId;

    private ?string $clientSecret;

    public function __construct(?string $mode, ?string $clientId, ?string $clientSecret)
    {
        $this->mode = $mode;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        if ($this->mode === 'sandbox') {
            $this->endpoint = static::ENDPOINT_SANDBOX;
        } else {
            $this->endpoint = static::ENDPOINT_LIVE;
        }
    }

    public function getAccessToken()
    {
        $request = $this->makeRequest();

        $response = $request

            ->withBasicAuth(
                $this->clientId,
                $this->clientSecret
            )

            ->asForm()

            ->post('v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        $token = @$response['access_token'];

        if (!$token) {
            Log::error('Could not get PayPal access token, got ', $response->json());
        }

        return $token;
    }

    /**
     * Creates PayPal Product
     */
    public function createProduct(string $name, string $description)
    {
        $route = 'v1/catalogs/products';

        $data = [
            'name' => $name,
            'description' => $description,
            "type" => "DIGITAL",
            "category" => "SOFTWARE"
        ];

        return $this->makeApiRequest()->post($route, $data);
    }

    public function getSubscription($subscription_id)
    {
        return $this
            ->makeApiRequest()
            ->get('/v1/billing/subscriptions/' . $subscription_id);
    }

    public function createOrder(
        $amount,
        $currency,
        $description,
        $custom_id,
        $return_url,
        $cancel_url,
        $brand_name
    ) {

        $amountRecord = [
            'currency_code' => $currency,
            'value' => $amount,
        ];

        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => array_merge($amountRecord, [
                        'breakdown' => [
                            'item_total' => $amountRecord,
                        ]
                    ]),
                    'description' => $description,
                    'custom_id' => $custom_id,
                    'items' => [
                        [
                            'name' => $description,
                            'quantity' => 1,
                            'unit_amount' => $amountRecord,
                            'category' => 'DIGITAL_GOODS'
                        ]
                    ]
                ]
            ],

            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        'payment_method_selected' => 'PAYPAL',
                        'brand_name' => $brand_name,
                        'return_url' => $return_url,
                        'cancel_url' => $cancel_url,
                        'user_action' => 'PAY_NOW',
                    ]
                ]
            ]
        ];

        $this->logDebugf('Sending create order request, %s', json_encode($data, JSON_PRETTY_PRINT));

        return $this->makeApiRequest()->withHeaders(
            ['PayPal-Request-Id' => sprintf('account-top-up:amount-%s:user-%s:time-%s', $amount, $custom_id, time())]
        )->post(
            '/v2/checkout/orders',
            $data
        )->json();
    }

    public function captureOrder($orderId)
    {
        return $this->makeApiRequest()->post(
            "/v2/checkout/orders/$orderId/capture",
            ['json' => []]
        )->json();
    }

    public function createSubscriptionPlan(
        string $paypal_product_id,
        string $name,
        string $description,
        string $interval,
        string $price,
        string $currency_code
    ) {
        $route = 'v1/billing/plans';

        $data  = [
            "product_id" => $paypal_product_id,
            "name" => $name,
            "description" => $description,
            "status" => "ACTIVE",
            "billing_cycles" => [
                [
                    "frequency" => [
                        "interval_unit" => $interval,
                        "interval_count" => 1
                    ],
                    "tenure_type" => "REGULAR",
                    "sequence" => 1,
                    "total_cycles" => 0,
                    "pricing_scheme" => [
                        "fixed_price" => [
                            "value" => $price,
                            "currency_code" => $currency_code
                        ]
                    ]
                ]
            ],
            "payment_preferences" => [
                "auto_bill_outstanding" => true,
                "payment_failure_threshold" => 3
            ]
        ];

        return $this->makeApiRequest()->post($route, $data)->json();
    }

    public function createSubscription(string $plan_id, $custom_id, $email, $app_name, $return_url, $cancel_url)
    {
        $route = '/v1/billing/subscriptions';

        $data = [
            'plan_id' => $plan_id,
            'custom_id' => $custom_id,
            'subscriber' => [
                'email_address' => $email
            ],
            'application_context' => [
                'return_url' => $return_url,
                'cancel_url' => $cancel_url,
                'brand_name' => $app_name,
            ]
        ];

        return $this->makeApiRequest()->post(
            $route,
            $data
        )->json();
    }

    public function getLink($links, $rel)
    {
        return collect($links)->first(fn($link) => $link['rel'] == $rel)['href'];
    }

    public function deactivatePlan(string $paypal_plan_id)
    {
        $route = "/v1/billing/plans/$paypal_plan_id/deactivate";

        return $this->makeApiRequest()->post($route);
    }

    public function listTransactions()
    {
        $start = Carbon::parse('now')->subMonth(1);

        $end = Carbon::now();

        return $this->makeApiRequest()->get('/v1/reporting/transactions', [
            'start_date' => $start->toIso8601String(),
            'end_date' => $end->toIso8601String()
        ])->json();
    }

    /**
     * Required to be called when app feature is enabled.
     */
    public function terminateToken()
    {
        $token = $this->getAccessToken();

        $this->makeRequest()
            ->withBasicAuth(
                $this->clientId,
                $this->clientSecret
            )->asForm()->acceptJson()
            ->post('v1/oauth2/token/terminate', [
                'token' => $token
            ]);
    }

    public function registerWebhook($url)
    {
        return $this->makeApiRequest()->post('v1/notifications/webhooks', [
            'url' => $url,
            'event_types' => [
                ['name' => 'PAYMENT.SALE.COMPLETED'],
                ['name' => 'PAYMENT.SALE.DENIED'],
                ['name' => 'PAYMENT.SALE.PENDING'],
                ['name' => 'CHECKOUT.ORDER.APPROVED'],
            ]
        ])->json();
    }

    public function clearWebhooks()
    {
        $webhooks = $this->listWebhooks();

        foreach ($webhooks as $webhook) {
            $this->deleteWebhook($webhook['id']);
        }

        return $this->listWebhooks();
    }

    public function deleteWebhook($id)
    {
        return $this->makeApiRequest()->delete('/v1/notifications/webhooks/' . $id);
    }

    public function listWebhooks()
    {
        return $this->makeApiRequest()->get('/v1/notifications/webhooks')->json()['webhooks'];
    }

    private function makeApiRequest()
    {
        $accessToken = $this->getAccessToken();

        return $this->makeRequest()->withToken($accessToken)->asJson();
    }

    private function makeRequest()
    {
        return Http::withHeaders([
            'Accept-Language' => 'en_US'
        ])
            ->acceptJson()
            ->timeout(10)
            ->baseUrl($this->endpoint);
    }
}
