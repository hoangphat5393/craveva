<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Scopes\CompanyScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = auth()->user();

        if ($authUser === null) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->guest(route('login'));
        }

        // Match `user()` helper: company-scoped profile first, then super admin without CompanyScope.
        // `auth()->user()->user` applies CompanyScope and returns null for super admins (company_id null)
        // while a company workspace is selected in session — dashboard uses `user()` and still works.
        $user = User::query()
            ->where('user_auth_id', $authUser->id)
            ->where('status', 'active')
            ->first();

        if ($user === null) {
            $user = User::withoutGlobalScope(CompanyScope::class)
                ->where('user_auth_id', $authUser->id)
                ->where('status', 'active')
                ->where('is_superadmin', 1)
                ->first();
        }

        if ($user === null) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden.'], 403)
                : redirect()->guest(route('login'));
        }

        abort_403((! $user->is_superadmin && ! $user->hasRole('admin')));

        return $next($request);
    }
}
