<?php

namespace App\Http\Middleware;

use App\Interfaces\TranslationManager;
use App\Support\System\Traits\WriteLogs;
use Closure;
use Illuminate\Http\Request;
use App\Models\Translation;
use Throwable;

class SyncAppLocaleFromCookie
{
    use WriteLogs;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('app.installed')) {
            return $next($request);
        }

        try {
            $translations = app(TranslationManager::class);

            /**
             * @var Translation
             */
            $translation = $translations->getCurrentTranslation();

            app()->setLocale($translation->locale);
        } catch (Throwable $th) {
            //
        }

        return $next($request);
    }
}
