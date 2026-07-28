<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): mixed  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Allow all origins
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
        header('Access-Control-Expose-Headers: Authorization');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 hours

        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200);
        }

        return $next($request);
    }
}
