<?php

namespace App\Http\Middleware;

// If you're using RouteServiceProvider for the home path:
// use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
                // Redirect to your intended dashboard route
                // This was the path we discussed earlier for logged-in users
                return redirect('/dashboard');

                // Or, if you used RouteServiceProvider and defined HOME constant:
                // return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}