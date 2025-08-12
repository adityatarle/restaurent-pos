<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ReceptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Reception can also be Super Admin
        if (Auth::check() && (Auth::user()->isReception() || Auth::user()->isSuperAdmin())) {
            return $next($request);
        }
        abort(403, 'Unauthorized action.');
    }
}