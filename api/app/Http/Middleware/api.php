<?php

namespace App\Http\Middleware;

use Closure;
use App\Extend\JWT;

class api
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct()
    {
        //$this->auth = $auth;
    }

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
        $key = ENV('JWT_SECRET');
        $all = $request->all();
        $obj = isset($all['token']) ? JWT::decode($all['token'], $key, array('HS256')) : 'token不得为空';
        if (!is_object($obj)) {
            $arr = [
                'code' => 4010,
                'msg' => $obj,
            ];
            echo json_encode($arr);
            exit;
        }
        return $next($request);
    }
}
