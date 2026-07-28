<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): mixed  $next
     * @param  string  ...$permissions  One or more permission names (user needs at least one)
     */
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user = Auth::user();

        if (! $user || ! $user->role) {
            return ApiResponse::forbidden('You do not have permission to access this resource');
        }

        // Load permissions for the user's role if not already loaded
        if (! $user->role->relationLoaded('permissions')) {
            $user->role->load('permissions');
        }

        // Check if the user's role has any of the required permissions
        $hasPermission = $user->role->permissions
            ->pluck('name')
            ->intersect($permissions)
            ->isNotEmpty();

        if (! $hasPermission) {
            return ApiResponse::forbidden('You do not have permission to access this resource');
        }

        return $next($request);
    }
}
