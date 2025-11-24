<?php

namespace App\Repositories;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Interfaces\PaymentGateway;

/**
 * @deprecated 
 * @see App\Support\PaymentProcessors\PayPalPaymentProcessor
 */
class PaypalPaymentGateway implements PaymentGateway
{
	private $currencies;

	function __construct()
	{
	}

	private function shouldRun()
	{
		return false;
	}

	public function getAccessToken()
	{
	}

	private function getProductId($subscriptionPlan)
	{
	}


	public function verifySubscription(Subscription $subscription)
	{
	}

	public function saveSubscriptionPlan(SubscriptionPlan $subscriptionPlan, bool $forceSync = false)
	{
	}

	public function deactivatePlan($subscriptionPlan)
	{
	}

	public function listTransactions()
	{
	}

	/**
	 * Required to be called when app feature is enabled.
	 */
	public function terminateToken()
	{
	}

	public function registerWebhook()
	{
	}

	public function clearWebhooks()
	{
	}

	public function deleteWebhook($id)
	{
	}

	public function listWebhooks()
	{
	}

	public static function boot()
	{
	}

	private static function bindConfiguration()
	{
	}

	private function makeApiRequest()
	{
	}

	private function makeRequest()
	{
	}
}
