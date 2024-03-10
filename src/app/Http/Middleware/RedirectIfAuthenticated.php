<?php

namespace AMoschou\RemoteAuth\App\Http\Middleware;

use AMoschou\RemoteAuth\App\Providers\ServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// This middleware class exactly replicates the class in a default Laravel app.

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(ServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}