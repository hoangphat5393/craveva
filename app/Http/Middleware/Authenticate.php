<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use App\Scopes\CompanyScope;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $companyHashId = $request->route('hash');
        $routeName = $request->route()->getName();

        if ($routeName === 'settings.qr-login') {

            $company = Company::where('hash', $companyHashId)->first();

            $qrEnable = DB::table('attendance_settings')
                ->where('company_id', $company->id)
                ->value('qr_enable');

            if ($qrEnable == 0) {
                abort(403, __('messages.qrDisabled'));
            }
        }

        if (user()) {
            $isActive = cache()->rememberForever('user_is_active_'.user()->id, function () {
                return User::withoutGlobalScope(CompanyScope::class)
                    ->where('id', user()->id)
                    ->where('status', 'active')
                    ->exists();
            });

            if (! $isActive) {
                auth()->logout();
                session()->flush();

                return redirect()->route('login');
            }
        }

        $this->authenticate($request, $guards);

        // Update user's last activity time after successful authentication
        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['last_activity' => now()]);
        }

        return $next($request);
    }
}
