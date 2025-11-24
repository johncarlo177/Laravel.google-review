<?php

namespace App\Support\PaymentProcessors;


use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Support\PaymentProcessors\Interfaces\CancelsSubscription;
use App\Support\PaymentProcessors\Interfaces\CanSyncSubscriptionPlans;
use App\Support\PaymentProcessors\Interfaces\ChangesSubscription;
use App\Support\PaymentProcessors\Interfaces\ForwardsApiCalls;
use App\Support\PaymentProcessors\Interfaces\ProcessAccountCreditBilling;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Throwable;

class PaymentProcessorManager
{
    use WriteLogs;

    public static function boot()
    {
        // 
    }

    public static function registerWebRoutes()
    {
        static::registerSelfHostedPayRoutes();
        static::registerChangeSubscriptionResultRoutes();
    }

    private static function registerChangeSubscriptionResultRoutes()
    {
        $manager = new static;

        $manager->enabledPaymentProcessors()->filter(

            function (PaymentProcessor $processor) {

                return $processor instanceof ChangesSubscription;
            }

        )->each(function (ChangesSubscription $processor) {

            Route::get(
                $processor::changeSubscriptionResultPageRoute(),
                [$processor::class, 'renderChangeSubscriptionResultPage']
            );
            //
        });
    }

    private static function registerSelfHostedPayRoutes()
    {
        $manager = new static;

        $classes = $manager->getSelfHostedClassNames();

        $classes->each(function ($class) {
            Route::get($class::payRoute(), [$class, 'renderPayRoute']);
        });
    }

    public static function getSlugs()
    {
        $manager = new static;

        return $manager->enabledPaymentProcessors()->map(fn($p) => $p->slug());
    }

    public function getAvailablePaymentProcessors()
    {
        return $this->enabledPaymentProcessors()
            ->map(fn($processor) => $this->paymentProcessorResponse($processor))
            ->values();
    }

    public function view($slug)
    {
        $processor  = $this->getBySlug($slug);

        return $this->paymentProcessorResponse($processor);
    }

    public function getBySlug($slug): PaymentProcessor
    {
        /** @var PaymentProcessor */
        $paymentProcessor = $this->enabledPaymentProcessors()->first(fn($processor) => $processor->slug() == $slug);

        if (!$paymentProcessor) {
            abort(404, 'Payment processor could not be found');
        }

        return $paymentProcessor;
    }

    public function find($slug): ?PaymentProcessor
    {
        return $this->enabledPaymentProcessors()->first(fn($processor) => $processor->slug() == $slug);
    }

    /**
     * Syncs all (not trial) subscription plans with enable payment processors. 
     */
    public function syncPlans()
    {
        $this->getEnabledCanSyncSubscriptionPlansPaymentProcessors()->each(function (
            /** @var PaymentProcessor */
            $paymentProcessor
        ) {
            $paymentProcessor->syncPlans();
        });
    }

    public function syncPlan(SubscriptionPlan $plan)
    {
        $this->getEnabledCanSyncSubscriptionPlansPaymentProcessors()->each(
            function (
                CanSyncSubscriptionPlans $paymentProcessor
            ) use ($plan) {
                $paymentProcessor->syncPlan($plan);
            }
        );
    }

    public function getEnabledCanSyncSubscriptionPlansPaymentProcessors()
    {
        return $this->enabledPaymentProcessors()->filter(function ($processor) {
            return $processor instanceof CanSyncSubscriptionPlans;
        });
    }

    public function accountCreditProcessor($slug): ?ProcessAccountCreditBilling
    {
        return $this->enabledPaymentProcessors()->filter(function ($processor) {
            return $processor instanceof ProcessAccountCreditBilling;
        })->first(fn(PaymentProcessor $p) => $p->slug() === $slug);
    }

    public function enabledPaymentProcessors()
    {
        $processors = $this->makePaymentProcessorsClassList();

        return collect($processors)
            ->map(fn($class) => new $class)
            ->filter(fn($processor) => $processor instanceof PaymentProcessor)
            ->filter(function ($processor) {
                try {
                    return $processor->enabled();
                } catch (Throwable $th) {
                    return false;
                }
            })
            ->sort(fn($p1, $p2) => $p1->sortOrder() - $p2->sortOrder())
            ->values();
    }

    private function paymentProcessorResponse(PaymentProcessor $processor)
    {
        return $processor->toArray();
    }

    private function getSelfHostedClassNames()
    {
        return collect($this->makePaymentProcessorsClassList())->filter(function ($class) {
            $obj = new $class;

            return $obj instanceof SelfHostedPaymentProcessor;
        });
    }

    public function forwardCall($slug, $method, $data)
    {
        $processor = $this->getBySlug($slug);

        $methodName = 'forwarded' . ucfirst($method);

        if (!$processor instanceof ForwardsApiCalls) {
            return abort(422, 'Cannot forward call to this payment processor');
        }

        if (!method_exists($processor, $methodName)) {
            abort(422, 'Method doesn\'t exist.');
        }

        $response = call_user_func([$processor, $methodName], $data);

        return $response;
    }

    private function makePaymentProcessorsClassList()
    {
        $files = array_map(
            function ($file) {
                $file = basename($file, '.php');

                return $file;
            },
            glob(__DIR__ . '/*.php')
        );

        $files = array_filter(
            $files,
            function ($file) {
                return !preg_match('/^PaymentProcessorManager$/', $file);
            }
        );

        $classes = array_map(fn($f) =>  __NAMESPACE__ . '\\' . $f, $files);

        // ignore all abstract classes.
        return array_filter($classes, function ($class) {
            $instance = new ReflectionClass($class);
            return !$instance->isAbstract();
        });
    }

    public function cancelSubscriptionForAllProcessors(Subscription $subscription)
    {
        $this->enabledPaymentProcessors()
            ->each(
                function (PaymentProcessor $processor) use ($subscription) {
                    $this->cancelSubscription(
                        $processor,
                        $subscription
                    );
                }
            );
    }

    private function cancelSubscription(
        PaymentProcessor $processor,
        Subscription $subscription
    ) {

        if (!($processor instanceof CancelsSubscription)) {
            return;
        }

        try {
            $this->logDebugf('Cancelling subscription with %s', $processor->slug());

            $response = $processor->cancelRemoteSubscription($subscription);

            $this->logDebugf('Done canceling subscription with %s, remote response is %s', $processor->slug(), json_encode($response, JSON_PRETTY_PRINT));
        } catch (Throwable $th) {
            $this->logErrorf(
                '%s: Error canceling subscription %s',
                $processor->slug(),
                $th->getMessage()
            );
        }
    }
}
