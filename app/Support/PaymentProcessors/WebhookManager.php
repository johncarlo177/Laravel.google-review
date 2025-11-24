<?php

namespace App\Support\PaymentProcessors;

use App\Http\Controllers\PaymentProcessorController;
use App\Plugins\PluginManager;
use Illuminate\Support\Facades\Route;

class WebhookManager
{
    public static function init()
    {
        return new static;
    }

    public function defineRoutes()
    {
        Route::post('/{slug}', [$this::class, 'handle'])
            ->where('slug', $this->getSlugPattern());

        Route::get('/{slug}', [$this::class, 'handle'])
            ->where('slug', $this->getSlugPattern());
    }

    protected function getSlugPattern()
    {
        return sprintf(
            '^(%s).*',
            PaymentProcessorManager::getSlugs()->join('|')
        );
    }

    protected function processors()
    {
        return new PaymentProcessorManager;
    }

    protected function isCustomWebhook($slug)
    {
        $isCustomWebhook = PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_WEBHOOK_IS_CUSTOM_WEBHOOK,
            false,
            $slug,
            request()
        );

        return $isCustomWebhook;
    }

    protected function handleCustomWebhook($slug)
    {
        $response = PluginManager::doFilter(
            PluginManager::FILTER_PAYMENT_PROCESSOR_WEBHOOK_CUSTOM_WEBHOOK_RESPONSE,
            '',
            $slug,
            request(),
        );

        return $response;
    }

    public function handle($slug)
    {
        if ($this->isCustomWebhook($slug)) {
            return $this->handleCustomWebhook($slug);
        }

        return $this->processors()->getBySlug($slug)->receiveWebhook(
            request()
        );
    }
}
