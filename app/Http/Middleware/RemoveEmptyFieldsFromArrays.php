<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RemoveEmptyFieldsFromArrays
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
        $all = $request->all();

        foreach ($all as $name => $value) {
            if (is_array($value)) {
                $request->merge([
                    $name => $this->filterData($value)
                ]);
            }
        }

        return $next($request);
    }

    private function filterData($data)
    {
        return array_reduce(array_keys($data), function ($result, $key) use ($data) {

            $value = $data[$key];

            if (is_string($value)) {
                $value = trim($value);
            }

            if (!empty($value) || is_bool($value)) {
                $result[$key] = $value;
            }

            return $result;
        }, []);
    }
}
