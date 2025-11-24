<?php

namespace App\Http\Middleware;

use App\Support\DatabaseHelper;
use App\Support\System\Traits\WriteLogs;
use Closure;
use Illuminate\Http\Request;

class BenchmarkMiddleware
{
    use WriteLogs;

    public static function isEnabled()
    {
        return false;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$this->isEnabled()) {
            return $next($request);
        }

        $response = $next($request);

        if (DatabaseHelper::$queryCount > -1) {
            $this->logDebug(
                '%s %s database queries',
                $request->path(),
                DatabaseHelper::$queryCount
            );
        }

        $this->logDebug(
            '%s seconds - %s',
            $this->getTime(),
            $request->path()
        );

        return $response;
    }

    private function getTime()
    {
        return microtime(true) - LARAVEL_START;
    }
}
