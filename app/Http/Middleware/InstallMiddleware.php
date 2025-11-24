<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallMiddleware
{
    protected function isDuringUpdate()
    {
        return file_exists(base_path('update.lock'));
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
        $installed = config('app.installed');

        if (!$installed && $this->isDuringUpdate()) {
            return response(
                'System update in progress, this could take a few minutes ...'
            );
        }

        if (!$installed && !$this->isInstallRoute($request)) {
            return redirect('/install');
        }

        if ($installed && $this->isInstallRoute($request)) {
            abort(404);
        }

        return $next($request);
    }

    private function isInstallRoute(Request $request)
    {
        $path = $request->path();

        return preg_match('/^install/', $path);
    }
}
