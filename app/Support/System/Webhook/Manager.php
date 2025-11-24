<?php

namespace App\Support\System\Webhook;

use App\Support\System\Traits\ClassListLoader;
use Illuminate\Http\Request;

class Manager
{
    use ClassListLoader;

    public function handle(Request $request)
    {
        $webhook = $this->resolveWebhook($request);

        return $webhook?->handle($request);
    }

    private function resolveWebhook(Request $request): ?BaseWebhook
    {
        return collect(
            $this->makeInstances(__DIR__)
        )->first(function (BaseWebhook $webhook) use ($request) {
            return $webhook->slug() === $request->slug;
        });
    }
}
