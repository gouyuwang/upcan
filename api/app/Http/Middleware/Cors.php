<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $headers = config('cors');
        $response = $next($request);
        $allow_origin = [
            'http://upcan.darongshutech.com',
            'http://upcan-api.darongshutech.com',
            'http://upcan.com',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
        ];
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, $allow_origin)) {
            header('Access-Control-Allow-Origin:' . $origin);
        } else {
            header('Access-Control-Allow-Origin:*');
        }
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }
        return $response;
    }
}
