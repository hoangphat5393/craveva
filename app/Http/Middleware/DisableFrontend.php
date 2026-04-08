<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableFrontend
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $global = global_setting();

        if (! $global || ! $global->frontend_disable) {
            return $next($request);
        }

        $route = $request->route();
        if (! $route || $route->getName() === 'front.signup.index' || $request->ajax()) {
            return $next($request);
        }

        return redirect(route('login'));
    }
}
