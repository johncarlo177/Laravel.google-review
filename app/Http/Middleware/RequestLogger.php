<?php

namespace App\Http\Middleware;

use App\Support\System\Traits\WriteLogs;
use Closure;
use Illuminate\Http\Request;

class RequestLogger
{
    use WriteLogs;

    protected function shouldLogRequests()
    {
        if (!app()->environment('local')) {
            return false;
        }

        return false;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->shouldLogRequests()) {
            return $next($request);
        }

        $this->logDebug('Request: %s', var_export($request, true));

        $response = $next($request);

        $this->logDebug('Response: %s', var_export($request, true));

        return $response;
    }
}
