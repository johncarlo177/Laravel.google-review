<?php

namespace App\Http\Middleware;

use Closure;
use stdClass;
use Illuminate\Http\Request;

class ErrorMessageMiddleware
{
    private static $message;

    public static function setMessage($message)
    {
        static::$message = $message;
    }

    public static function abortWithMessage($message, $code = 403)
    {
        static::setMessage($message);

        abort($code);
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
        $response = $next($request);

        if (empty($this::$message)) {
            return $response;
        }

        $content = $response->getContent();

        $json = json_decode($content) ?? new stdClass;

        $json->error_message = $this::$message;

        $response->setContent(json_encode($json));

        return $response;
    }
}
