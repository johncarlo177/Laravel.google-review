<?php

use App\Support\AI\QuickQrArtWebhookHandler;

use Illuminate\Support\Facades\Route;;

use App\Support\System\Webhook\Manager as QuickCodeWebhookManager;

Route::post('quickcode', [QuickCodeWebhookManager::class, 'handle']);

Route::post('quickqrart', [QuickQrArtWebhookHandler::class, 'handle']);
