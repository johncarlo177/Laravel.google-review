<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Http\Controllers\QRCodeRedirectController;
use App\Models\Domain;
use App\Models\QRCodeRedirect;
use App\Support\DomainManager;
use App\Support\QRCodeTypes\ViewComposers\Base as QRCodeViewComposer;
use App\Support\System\Traits\WriteLogs;

class CustomDomainServer
{
    use WriteLogs;

    private static $excludedPatterns = [];

    private Request $request;

    public static function excludePattern($pattern)
    {
        static::$excludedPatterns[] = $pattern;
    }

    private static function getRequestHost()
    {
        $host = request()
            ->header(
                'X-Forwarded-Host',
                static::getHost(request()->fullUrl())
            );

        return preg_replace('/^www\./', '', $host);
    }

    public static function servingCustomDomain()
    {
        if (isLocal()) return false;

        $requestHost = static::getRequestHost();

        $configHost = static::getHost(config('app.url'));

        return $requestHost != $configHost;
    }

    private static function getHost($url)
    {
        $host = parse_url($url)['host'];

        return preg_replace('/^www\./', '', $host);
    }

    private function servingScanRoute()
    {
        foreach (QRCodeRedirectController::SCAN_PREFIX as $prefix) {
            if (preg_match("/^$prefix\//", $this->request->path())) {
                return true;
            }
        }

        $slug = $this->request->path();

        $found = QRCodeRedirect::whereSlug($slug)->first();

        return !empty($found);
    }

    private function servingDomainConnectionString()
    {
        return $this->request->path() === DomainManager::DOMAIN_CONNECTION_ROUTE;
    }

    /**
     * @return Domain
     */
    private function findCustomDomain()
    {
        $host = $this->request->host();

        $non_www = str_replace('www.', '', $host);

        $www = 'www.' . $host;

        /** @var Domain */
        $domain = Domain::with('homePageQRCode')
            ->where(
                'host',
                $host
            )
            ->orWhere(
                'host',
                $non_www
            )
            ->orWhere(
                'host',
                $www
            )
            ->first();

        return $domain;
    }

    private function renderCustomDomainHomePage()
    {
        /** @var Domain */
        $domain = $this->findCustomDomain();

        $slug = $domain?->homePageQRCode?->redirect?->slug;

        if (empty($slug)) {
            return $this->defaultDomainResponse();
        }

        QRCodeViewComposer::resolveQRCode($domain->homePageQRCode);

        return response(
            QRCodeRedirectController::serveSlug(
                $slug
            )
        );
    }

    private function isHomePage()
    {
        return $this->request->path() === '/' || empty($this->request->path());
    }

    private function isPatternExcluded()
    {
        return collect($this::$excludedPatterns)->reduce(
            function ($result, $pattern) {
                return $result || preg_match(
                    $pattern,
                    $this->request->path()
                );
            },
            false
        );
    }

    private function defaultDomainResponse()
    {
        return response()->view('custom-domain');
    }

    protected function isServingWhastAppOrderRoutes()
    {
        return preg_match('/^whatsapp-order/', $this->request->path());
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
        $this->request = $request;

        if (!static::servingCustomDomain()) {
            return $next($request);
        }

        // If no Domain record exists for this host, pass through normally
        // This prevents showing the custom-domain message when the domain
        // doesn't match APP_URL but isn't actually a configured custom domain
        $domain = $this->findCustomDomain();
        if (empty($domain)) {
            return $next($request);
        }

        if ($this->isPatternExcluded()) {
            return $next($request);
        }

        if ($this->servingScanRoute()) {
            return $next($request);
        }

        if ($this->servingDomainConnectionString()) {
            return $next($request);
        }

        if ($this->isServingWhastAppOrderRoutes()) {
            return $next($request);
        }

        if ($this->isHomePage($request)) {
            return $this->renderCustomDomainHomePage();
        }

        return $this->defaultDomainResponse();
    }
}
