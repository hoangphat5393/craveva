<?php

namespace App\Listeners;

use App\Models\Company;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Login $event)
    {

        if (! session()->has('impersonate') && ! session()->has('stop_impersonate')) {
            $authUserId = $event->user?->id;

            if (! $authUserId) {
                return;
            }

            $user = User::withoutGlobalScope(CompanyScope::class)
                ->where('user_auth_id', $authUserId)
                ->where('status', 'active')
                ->first();

            if (! $user) {
                return;
            }

            $user->last_login = now();
            /* @phpstan-ignore-line */
            $user->save();

            if ($user->company_id) {
                $company = Company::find($user->company_id);

                if (! $company) {
                    return;
                }

                session(['company' => $company]);
                $company->last_login = now();  /* @phpstan-ignore-line */
                $company->saveQuietly();
            }
        }
    }
}
