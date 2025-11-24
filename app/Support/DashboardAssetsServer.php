<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class DashboardAssetsServer
{
    const ROUTE = '/integration/dashboard-assets.js';

    const AGE_IN_DAYS = 7;

    public static function registerWebRoute()
    {
        Route::get(static::ROUTE, [static::class, 'serve']);
    }

    private function templates()
    {
        return [
            'blue.partials.head.dashboard-assets',
            'blue.partials.head.configs',
            'blue.partials.head.dashboard-styles',
        ];
    }

    private function combinedTemplates()
    {
        return collect($this->templates())->map(function ($path) {
            return view($path)->render();
        })->join("\n");
    }

    private function maxAge()
    {
        // in seconds
        return $this::AGE_IN_DAYS * 24 * 3600;
    }

    private function serveDemo()
    {
        $jsScript = "(function() {
            alert('Demo script cannot be used with your integration');
        })()";

        return response($jsScript, 200, [
            'Content-Type' => 'text/javascript',
            'Cache-Control' => 'max-age=' . $this->maxAge()
        ]);
    }

    public function serve()
    {
        if (app()->environment('demo')) {
            return $this->serveDemo();
        }

        $data = $this->combinedTemplates();

        $data = base64_encode($data);

        $jsScript = "(function() {
           
            const html = window.atob('$data');

            const divFragment = document.createRange().createContextualFragment(html);

            document.body.prepend(divFragment);

        })()";

        return response($jsScript, 200, [
            'Content-Type' => 'text/javascript',
            'Cache-Control' => 'max-age=' . $this->maxAge()
        ]);
    }
}
