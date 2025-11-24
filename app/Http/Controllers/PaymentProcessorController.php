<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSubscriptionPlans;
use App\Models\SubscriptionPlan;
use App\Support\PaymentProcessors\Interfaces\CanSyncSubscriptionPlans;
use App\Support\PaymentProcessors\Interfaces\RegistersWebhook;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class PaymentProcessorController extends Controller
{
    use WriteLogs;

    private PaymentProcessorManager $paymentProcessorManager;

    public function __construct(PaymentProcessorManager $paymentProcessorManager)
    {
        $this->paymentProcessorManager = $paymentProcessorManager;
    }

    public function index()
    {
        return $this->paymentProcessorManager->getAvailablePaymentProcessors();
    }

    public function forwardCall($processorSlug, $method, Request $request)
    {
        return $this->paymentProcessorManager->forwardCall($processorSlug, $method, $request->all());
    }

    public function view($slug)
    {
        return $this->paymentProcessorManager->view($slug);
    }

    public function generatePayLink($processorSlug, SubscriptionPlan $plan)
    {
        return [
            'link' => $this
                ->paymentProcessorManager
                ?->getBySlug($processorSlug)
                ->generatePayLink(request()->user(), $plan)
        ];
    }

    public function createChargeLink($processorSlug, $amount)
    {
        return [
            'link' => $this->paymentProcessorManager->accountCreditProcessor($processorSlug)?->createChargeLink(request()->user(), $amount)
        ];
    }

    public function testCredentials($processorSlug)
    {
        $processor = $this->paymentProcessorManager->getBySlug($processorSlug);

        $result = $processor->testCredentials();

        if ($result['success'] && $processor instanceof CanSyncSubscriptionPlans) {

            $this->logInfo('Dispatching SyncSubscriptionPlans job');

            SyncSubscriptionPlans::dispatch($processorSlug);
        } else {
            $this->logInfo('Processor [%s] cannot sync plans', $processorSlug);
        }

        return $result;
    }

    public function handleWebhook($slug, Request $request)
    {
        return $this->paymentProcessorManager->getBySlug($slug)->receiveWebhook($request);
    }

    public function getWebhook($slug, Request $request)
    {
        return $this->paymentProcessorManager->getBySlug($slug)->getWebhook(
            $request
        );
    }

    public function registerWebhook($processorSlug)
    {
        $processor = $this
            ->paymentProcessorManager
            ->getBySlug($processorSlug);

        if ($processor instanceof RegistersWebhook) {
            return [
                'success' => $processor->registerWebhook()
            ];
        }

        return [
            'success' => false
        ];
    }
}
