<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            // no idea how to test this bit sorry
            // @codeCoverageIgnoreStart
            return redirect(route('tokens.code')); // no home so change to codes
            // @codeCoverageIgnoreEnd
        }

        return $next($request);
    }
}
