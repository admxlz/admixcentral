<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class EnsureSystemIsSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't intercept static assets or API calls generally, but here we want to block UI access
        // Avoid intercepting debugbar or similar if present

        $userCount = User::count();
        $isSetupRoute = $request->routeIs('setup.*');

        if ($userCount === 0) {
            // System is NOT setup
            if (!$isSetupRoute && !$request->routeIs('debugbar.*') && !$request->is('sanctum/*')) {
                return redirect()->route('setup.welcome');
            }
        } else {
            // System IS setup
            if ($isSetupRoute) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
