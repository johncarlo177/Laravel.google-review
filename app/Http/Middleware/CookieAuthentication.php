<?php

namespace App\Http\Middleware;

use App\Support\System\Traits\WriteLogs;
use Closure;
use Illuminate\Http\Request;

class CookieAuthentication
{
    use WriteLogs;

    protected Request $request;

    public function handle(Request $request, Closure $next)
    {
        $this->request = $request;

        if (preg_match('/^api/', $request->path())) {

            // 
            $this->addTokenCookie();
            // 
        } else {

            // 
            $this->authenticateBasedOnTokenCookie();
            // 
        }

        return $next($request);
    }

    protected function addTokenCookie()
    {
        $token = $this->request->header('Authorization');

        cookie()->queue('token', $token, 0);
    }

    protected function authenticateBasedOnTokenCookie()
    {
        $token = $this->request->cookie('token');

        if ($token) {
            $this->request->headers->add(
                [
                    'Authorization' => $token
                ]
            );

            $this->request->setUserResolver(function () {
                return auth('sanctum')->user();
            });
        }
    }
}
