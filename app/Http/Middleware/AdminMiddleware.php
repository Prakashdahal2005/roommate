<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('is_admin')) {
            abort(403, 'Unauthorized: Admins only.');
        }

        return $next($request);
    }
}
