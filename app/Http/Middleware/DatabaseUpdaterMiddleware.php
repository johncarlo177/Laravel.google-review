<?php

namespace App\Http\Middleware;

use App\Support\SoftwareUpdate\DatabaseUpdateManager;
use App\Support\System\Traits\WriteLogs;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class DatabaseUpdaterMiddleware
{
    use WriteLogs;

    private DatabaseUpdateManager $databaseUpdateManager;

    public function __construct()
    {
        $this->databaseUpdateManager = new DatabaseUpdateManager;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!config('app.installed')) return $next($request);

        try {
            if ($this->databaseUpdateManager->hasDatabaseUpdate()) {
                $this->databaseUpdateManager->updateDatabaseIfNeeded();
            }
        } catch (Throwable $th) {
            //
            $this->logDebug($th->getMessage());
        }

        return $next($request);
    }
}
