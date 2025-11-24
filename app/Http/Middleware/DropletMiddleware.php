<?php

namespace App\Http\Middleware;

use App\Support\DropletManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class DropletMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $dropletManager = new DropletManager;

        if (!$dropletManager->didRun()) {
            try {
                $dropletManager->verify();
            } catch (Throwable $th) {
                $shouldDebug = false;

                if ($shouldDebug) {
                    Log::error($th->getMessage());
                    Log::error($th->getTraceAsString());
                }
            }
        }

        return $next($request);
    }
}
