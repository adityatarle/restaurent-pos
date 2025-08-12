<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WaiterMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Waiter can also be Super Admin (or even Reception if you want that flexibility)
        if (Auth::check() && (Auth::user()->isWaiter() || Auth::user()->isSuperAdmin())) {
            return $next($request);
        }
        abort(403, 'Unauthorized action.');
    }
}