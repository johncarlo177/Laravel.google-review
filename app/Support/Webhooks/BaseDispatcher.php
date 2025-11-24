<?php

namespace App\Support\Webhooks;

use App\Support\System\MemoryCache;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

abstract class BaseDispatcher
{
    use WriteLogs;

    protected abstract function event();

    protected abstract function getPayload();

    protected abstract function getPayloadType();

    /**
     * @return Collection
     */
    protected function urls()
    {
        return MemoryCache::remember(__METHOD__, function () {
            return collect(
                range(1, 7)
            )
                ->map(function ($i) {
                    $key = "events.webhook_url_$i";

                    return config($key);
                })
                ->filter()
                ->values();
        });
    }

    protected function shouldDispatch()
    {
        return $this->isEventEnabled() && $this->urls()->isNotEmpty();
    }

    protected function isEventEnabled()
    {
        $selectedEvents = collect(config('events.selected_events'));

        return $selectedEvents->filter(
            fn($e) => $e === $this->event()
        )->isNotEmpty();
    }

    protected function buildPayload()
    {
        return [
            'event' => $this->event(),
            'timestamp' => time(),
            'data' => $this->getPayload(),
            'data_type' => $this->getPayloadType(),
        ];
    }

    protected function send()
    {
        $this->urls()->each(
            function ($url) {
                try {
                    Http::asJson()->post(
                        $url,
                        $this->buildPayload()
                    );
                    // 
                } catch (Throwable $th) {

                    $this->logWarning(
                        'Could not notify webhook endpoint (%s)',
                        $url
                    );

                    $this->logWarning(
                        $th->getMessage()
                    );
                }
            }
        );
    }

    public function dispatch()
    {
        if (!$this->shouldDispatch()) {
            return;
        }

        dispatch($this->send(...))->afterResponse();
    }
}
