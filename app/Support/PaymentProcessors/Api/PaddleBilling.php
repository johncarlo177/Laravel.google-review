<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use Exception;
use Illuminate\Support\Facades\Http;

class PaddleBilling
{
    use WriteLogs;

    public const INTERVAL_MONTHLY = 'month';
    public const INTERVAL_YEARLY = 'year';

    public const EVENT_TRANSACTION_COMPLETED = 'transaction.completed';

    private $apiKey, $isSandbox = true;

    private $jsonResponse;

    private $enableLogs = false;

    private static $instance = null;

    public static function instance($apiKey, $isSandbox)
    {
        if (!static::$instance)
            static::$instance = new static($apiKey, $isSandbox);

        return static::$instance;
    }

    private function __construct($apiKey, $isSandbox)
    {
        $this->apiKey = $apiKey;

        $this->isSandbox = $isSandbox;
    }

    private function baseUrl()
    {
        if ($this->isSandbox) {
            return 'https://sandbox-api.paddle.com';
        }

        return 'https://api.paddle.com';
    }

    public function listWebhookTypes()
    {
        return $this->get('event-types');
    }

    public function createProduct($name)
    {
        return $this->post('products', [
            'name' => $name,
            'tax_category' => 'standard'
        ]);
    }

    public function createOneTimePrice(
        $description,
        $product_id,
        $amount,
        $currency = 'USD',
    ) {
        return $this->post('prices', [
            'description' => $description,
            'product_id' => $product_id,
            'unit_price' => [
                'amount' => ($amount * 100) . '',
                'currency_code' => $currency
            ],
        ]);
    }

    public function createPrice(
        $description,
        $product_id,
        $amount,
        $interval,
        $frequency,
        $currency = 'USD',
    ) {
        return $this->post('prices', [
            'description' => $description,
            'product_id' => $product_id,
            'unit_price' => [
                'amount' => ($amount * 100) . '',
                'currency_code' => $currency
            ],
            'billing_cycle' => [
                'interval' => $interval,
                'frequency' => $frequency
            ]
        ]);
    }

    public function createNotificationSettings(
        $description,
        $destination,
        $subscribed_events
    ) {
        return $this->post('notification-settings', [
            'description' => $description,
            'type' => 'url',
            'destination' => $destination,
            'subscribed_events' => $subscribed_events
        ]);
    }

    public function listNotificationSettings()
    {
        return $this->get('notification-settings');
    }

    public function isNotificationRegistered($url)
    {
        $list = @$this->listNotificationSettings()->json()['data'];

        if (!is_array($list)) return false;

        return collect($list)->filter(fn($item) => @$item['destination'] == $url)->isNotEmpty();
    }

    public function getSubscription($subscription_id)
    {
        return $this->get('subscriptions/' . $subscription_id);
    }

    public function createTransaction($price_id, $quantity = 1)
    {
        return $this->post('transactions', [
            'items' => [
                [
                    'price_id' => $price_id,
                    'quantity' => $quantity
                ]
            ]
        ]);
    }

    private function get($endpoint)
    {
        $this->setJsonResponse($this->request()->get($endpoint)->json());

        return $this;
    }

    private function post($endpoint, $data)
    {
        $this->setJsonResponse(
            $this->request()
                ->post($endpoint, $data)
                ->json()
        );

        return $this;
    }

    private function request()
    {
        return Http::baseUrl($this->baseUrl())->acceptJson()->asJson()->withToken($this->apiKey);
    }

    private function getJsonResponse()
    {
        if (@!$this->jsonResponse) {
            throw new Exception('You must fetch api endpoint before trying to get the json response');
        }

        return $this->jsonResponse;
    }

    public function json()
    {
        return $this->getJsonResponse();
    }

    private function setJsonResponse($json)
    {
        if ($this->enableLogs) {
            $this->logDebug('API Response: %s', json_encode($json, JSON_PRETTY_PRINT));
        }

        $this->jsonResponse = $json;
    }

    public function cancelSubscription($id)
    {
        return $this->request()->post("subscriptions/$id/cancel", [
            'effective_from' => 'immediately'
        ]);
    }

    public function updateSubscription($subscriptionId, $priceId, $quantity = 1, $mode = 'prorated_immediately')
    {
        return $this->request()->patch(
            "subscriptions/$subscriptionId",
            [
                'items' => [
                    'price_id' => $priceId,
                    'quantity' => $quantity,
                ],
                'proration_billing_mode' => $mode,
            ]
        );
    }

    public function hasError()
    {
        return isset($this->getJsonResponse()['error']);
    }

    public function success()
    {
        return !$this->hasError();
    }
}
