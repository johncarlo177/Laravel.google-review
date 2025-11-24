<?php

namespace App\Support\PaymentProcessors\Traits;

use App\Models\Subscription;
use Illuminate\Http\Request;

trait RendersSelfHostedRoutes
{
    protected function makePayLink(Subscription $subscription)
    {
        return url($this::payRoute() . '?subscription_id=' . $subscription->id);
    }

    public static function payRoute(): string
    {
        $class = get_called_class();

        return '/payment/processor/' . (new $class())->slug();
    }

    public function renderPayRoute(Request $request)
    {
        $subscription = Subscription::findOrFail($request->subscription_id);

        $currency = $this->currencyManager->enabledCurrency()->symbol;

        $currencySymbolPosition = $this->currencyManager->enabledCurrency()->symbol_position;

        $processor = new static;

        return view(
            sprintf('payment.processor.%s', $this->slug()),
            compact(
                'subscription',
                'currency',
                'processor',
                'currencySymbolPosition',
            )
        );
    }
}
