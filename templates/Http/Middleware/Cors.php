<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if( ! $this->app->environment('testing')) {
            // referer and origin are returning null
            // TODO: need to check host with array from . env('FRONTEND_URL'));
            header('Access-Control-Allow-Origin: ' . $request->headers->get('origin') . "");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE');
            header('Access-Control-Max-Age: 3600');
            header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With, remember-me, Authorization');
        }
        
        return $next($request);
    }
}
