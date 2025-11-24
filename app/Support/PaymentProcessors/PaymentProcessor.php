<?php

namespace App\Support\PaymentProcessors;

use App\Interfaces\CurrencyManager;
use App\Interfaces\FileManager;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionStatus;
use App\Models\User;
use App\Interfaces\SubscriptionManager;
use App\Interfaces\TransactionManager;
use App\Interfaces\UserManager;
use App\Models\Config;
use App\Models\File;
use App\Models\Transaction;
use App\Plugins\PluginManager;
use App\Support\Billing\AccountCreditBillingManager;
use App\Support\Billing\BillingManager;
use App\Support\Invoicing\InvoiceManager;
use App\Support\PaymentProcessors\Interfaces\ChangesSubscription;
use App\Support\PaymentProcessors\Interfaces\HasCustomThankYouPage;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class PaymentProcessor
{
    use WriteLogs;

    protected UserManager $users;

    protected CurrencyManager $currencyManager;

    protected SubscriptionManager $subscriptionManager;

    protected TransactionManager $transactionManager;

    protected FileManager $fileManager;

    protected BillingManager $billing;

    protected AccountCreditBillingManager $accountCredit;

    protected $testCredentialMessages = [];

    public function __construct()
    {
        $this->currencyManager = app(CurrencyManager::class);

        $this->subscriptionManager = app(SubscriptionManager::class);

        $this->transactionManager = app(TransactionManager::class);

        $this->fileManager = app(FileManager::class);

        $this->billing = app(BillingManager::class);

        $this->accountCredit = app(AccountCreditBillingManager::class);

        $this->users = app(UserManager::class);
    }

    protected function getBillingDetailsResponseId()
    {
        $value = config('billing_collection_enabled');

        if ($value != 'enabled') {
            return;
        }

        $billingDetailsResponseId = request()->input('billingDetailsResponseId');

        return $billingDetailsResponseId;
    }

    public final function generatePayLink(User $user, SubscriptionPlan $plan)
    {
        $subscription = $this->createPendingSubscription($user, $plan, $this->getBillingDetailsResponseId());

        $price = $this->price($subscription);

        if ($price == 0) {

            $link = $this->applyPaylinkFilter(
                url('/dashboard/qrcodes'),
                $subscription
            );

            $this->subscriptionManager->activateSubscription($subscription);

            return $link;
        }

        $changePlanUrl = $this->generateChangeSubscriptionLinkIfNeeded($subscription);

        $paylink = $this->generatePayLinkIfNeeded($subscription);

        return $changePlanUrl ?? $paylink;
    }

    private function generateChangeSubscriptionLinkIfNeeded(Subscription $subscription)
    {
        if (request()->input('action') !== 'change-plan') return null;

        if (!($this instanceof ChangesSubscription)) {
            $this->logDebugf('%s does not implement ChangeSubscription interface', $this->slug());
            return null;
        }

        if (!$this->canChangeSubscription($subscription->user)) {
            return null;
        }

        $link = $this->generateChangeSubscriptionLink(
            user: $subscription->user,
            toPlan: $subscription->subscription_plan,
            onSuccess: function () use ($subscription) {
                $this->subscriptionManager->activateSubscription($subscription);
            }
        );

        return $link;
    }

    abstract protected function makePayLink(Subscription $subscription);

    protected function generatePayLinkIfNeeded(Subscription $subscription)
    {
        $shouldGenerate = true;

        $shouldGenerate = PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_SHOULD_GENERATE_PAY_LINK,
            $shouldGenerate,
            $subscription,
            static::class
        );

        if ($shouldGenerate) {
            $paylink = $this->makePayLink($subscription);
        } else {
            $paylink = null;
        }

        $paylink = $this->applyPaylinkFilter($paylink, $subscription);

        return $paylink;
    }

    private function applyPaylinkFilter($paylink, Subscription $subscription)
    {
        return PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_PAY_LINK,
            $paylink,
            $subscription,
            static::class,
        );
    }

    public function receiveWebhook(Request $request)
    {
        Log::info('Receiving webhook. ' . $this->slug());

        if (!$this->verifyWebhook($request)) {
            Log::warning('Webhook is NOT verified. ' . $this->slug());
            return;
        }

        Log::debug('Webhook is verified');

        return $this->handleVerifiedWebhook($request);
    }

    /**
     * Response when HTTP GET request is made to 
     * the webhook URL
     */
    public function getWebhook(Request $request)
    {
        return '';
    }

    abstract public function slug();

    abstract protected function verifyWebhook(Request $request): bool;

    abstract protected function handleVerifiedWebhook(Request $request);

    public function displayName()
    {
        return $this->config('display_name');
    }

    public function sortOrder()
    {
        return $this->config('sort_order');
    }

    public function payButtonText()
    {
        return $this->config('pay_button_text');
    }

    protected function createPendingSubscription(
        User $user,
        SubscriptionPlan $plan,
        $billingDetailsResponseId = null
    ): Subscription {

        $subscription = $this->subscriptionManager->saveSubscription([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'subscription_status' => SubscriptionStatus::STATUS_PENDING_PAYMENT,
            'billing_details_custom_form_response_id' => $billingDetailsResponseId
        ]);

        $subscription = PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_PENDING_SUBSCRIPTION,
            $subscription,
            static::class
        );

        return $subscription;
    }

    public function successUrl()
    {
        return route('payment.success', [
            'payment_gateway' => $this->slug()
        ]);
    }

    public function canceledUrl()
    {
        return route('payment.canceled');
    }

    public function webhookUrl()
    {
        return url(sprintf('/webhooks/%s', $this->slug()));
    }

    public function createTransaction(
        $remote_transaction_id,
        $subscription_id,
        $amount,
        $currency,
        $status,
        $user_id = null,
        $description = null
    ) {

        $subscription = Subscription::find($subscription_id);

        $subscription->touch();

        $transaction = $this->transactionManager->createTransaction(
            $subscription_id,
            $amount,
            $currency,
            $this->slug(),
            $status,
            $user_id,
            $description
        );

        $this->setRemoteTransactionId($transaction, $remote_transaction_id);

        InvoiceManager::withTransaction($transaction)
            ->generateInvoice();

        return $transaction;
    }

    protected function setRemoteTransactionId(Transaction $transaction, $remote_id)
    {
        $transaction->setMeta(
            sprintf(
                '%s.%s_id',
                $this->slug(),
                $this->remoteTransactionIdMetaKey()
            ),
            $remote_id
        );
    }

    protected function remoteTransactionIdMetaKey()
    {
        return 'transaction';
    }

    public function config($key)
    {
        $value = Config::get($this->configKey($key));

        return $value;
    }

    protected function configFilePath($key)
    {
        $fileId = $this->config($key);

        $file = File::find($fileId);

        if (!$file) {
            return null;
        }

        return $this->fileManager->path($file);
    }

    protected function configKey($key)
    {
        return sprintf(
            '%s.%s.%s',
            'payment_processors',
            $this->slug(),
            $key
        );
    }

    public function setConfig($key, $value)
    {
        return Config::set($this->configKey($key), $value);
    }

    protected abstract function doTestCredentials(): bool;

    protected function getTestCredentialsMessages()
    {
        return $this->testCredentialMessages;
    }

    public function testCredentials()
    {
        return [
            'success' => $this->doTestCredentials(),
            'messages' => $this->getTestCredentialsMessages()
        ];
    }

    public function enabled()
    {
        return $this->config('enabled');
    }

    public function clientFields()
    {
        return [];
    }

    protected function setSubscriptionId(Subscription $subscription, $id)
    {
        $subscription->setMeta($this->slug() . '_subscription_id', $id);
    }

    protected function getSubscriptionId(Subscription $subscription)
    {
        $subscription->getMeta($this->slug() . '_subscription_id');
    }

    public function toArray()
    {
        return [
            'slug' => $this->slug(),
            'display_name' => $this->displayName(),
            'pay_button_text' => $this->payButtonText(),
            'client_fields' => $this->clientFields()
        ];
    }

    public function thankYouViewPath(): ?string
    {
        if ($this instanceof HasCustomThankYouPage && $this->shouldRenderCustomThankYouPage()) {
            return sprintf('payment.thankyou.%s', $this->slug());
        }

        return null;
    }

    protected function enabledCurrencyCode()
    {
        return $this->currencyManager->enabledCurrency()->currency_code;
    }

    protected function price(Subscription $subscription)
    {
        $price = $subscription->subscription_plan->price;

        return PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_PLAN_PRICE,
            $price,
            $subscription->subscription_plan
        );
    }

    protected function planDescription(Subscription $subscription)
    {
        $description = $subscription->subscription_plan->description;

        return PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_PLAN_DESCRIPTION,
            $description,
            $subscription->subscription_plan
        );
    }

    protected function planName(Subscription $subscription)
    {
        $name = $subscription->subscription_plan->name;

        return PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_PLAN_NAME,
            $name,
            $subscription->subscription_plan
        );
    }

    public function currencyCode()
    {
        return $this->currencyManager->enabledCurrency()->currency_code;
    }
}
