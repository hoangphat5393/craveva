<?php

namespace App\Providers;

use App\Actions\Fortify\AttemptToAuthenticate;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\RedirectIfTwoFactorConfirmed;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\LanguageSetting;
use App\Models\SuperAdmin\FooterMenu;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\FrontMenu;
use App\Models\SuperAdmin\FrontWidget;
use App\Models\User;
use App\Models\UserAuth;
use App\Scopes\CompanyScope;
use Carbon\Carbon;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    use AppBoot;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request)
            {
                Log::info('LoginResponse triggered', ['wantsJson' => $request->wantsJson(), 'ajax' => $request->ajax()]);
                $authUser = auth()->user();
                $appUser = $authUser?->userWithoutCompany ?? $authUser?->user;

                if (! $appUser) {
                    Log::warning('LoginResponse: No app user found');
                    if ($request->wantsJson()) {
                        return response()->json([
                            'status' => 'success',
                            'action' => 'redirect',
                            'url' => route('login'),
                        ]);
                    }

                    return redirect()->route('login');
                }

                session(['user' => User::withoutGlobalScope(CompanyScope::class)->find($appUser->id)]);
                Log::info('LoginResponse: Session set for user', ['id' => $appUser->id]);

                $redirectUrl = '';

                if ($appUser->is_superadmin) {
                    $redirectUrl = RouteServiceProvider::SUPER_ADMIN_HOME;
                } else {
                    $emailCountInCompanies = DB::table('users')->where('email', $appUser->email)->count();
                    session()->forget('user_company_count');

                    if ($emailCountInCompanies > 1) {
                        if (module_enabled('Subdomain')) {
                            UserAuth::multipleUserLoginSubdomain();
                            $redirectUrl = session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME;
                        } else {
                            session(['user_company_count' => $emailCountInCompanies]);
                            $redirectUrl = route('superadmin.superadmin.workspaces');
                        }
                    } else {
                        $redirectUrl = session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME;
                    }
                }

                Log::info('LoginResponse: Redirecting', ['url' => $redirectUrl]);

                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'action' => 'redirect',
                        'url' => $redirectUrl,
                    ]);
                }

                return redirect($redirectUrl);
            }
        });

        $this->app->instance(TwoFactorLoginResponse::class, new class implements TwoFactorLoginResponse
        {
            public function toResponse($request)
            {
                $authUser = auth()->user();
                $appUser = $authUser?->userWithoutCompany ?? $authUser?->user;

                if (! $appUser) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'status' => 'success',
                            'action' => 'redirect',
                            'url' => route('login'),
                        ]);
                    }

                    return redirect()->route('login');
                }

                session(['user' => User::withoutGlobalScope(CompanyScope::class)->find($appUser->id)]);

                $redirectUrl = '';

                if ($appUser->is_superadmin) {
                    $redirectUrl = RouteServiceProvider::SUPER_ADMIN_HOME;
                } else {
                    $emailCountInCompanies = DB::table('users')->where('email', $appUser->email)->count();
                    session(['user_company_count' => $emailCountInCompanies]);

                    if ($emailCountInCompanies > 1) {
                        $redirectUrl = route('superadmin.superadmin.workspaces');
                    } else {
                        $redirectUrl = session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME;
                    }
                }

                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'action' => 'redirect',
                        'url' => $redirectUrl,
                    ]);
                }

                return redirect($redirectUrl);
            }
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse
        {
            public function toResponse($request)
            {
                session()->flush();

                return redirect()->route('login');
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (request()->has('locale')) {
            App::setLocale(request()->locale);
        }

        Fortify::authenticateThrough(function (Request $request) {

            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorConfirmed::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Fortify::authenticateThrough();
        Fortify::authenticateUsing(function (Request $request) {
            Log::info('AuthenticateUsing triggered', ['email' => $request->email]);
            $rules = [
                'email' => 'required|email:rfc,strict',
            ];

            $request->validate($rules);

            $userAuth = UserAuth::where('email', $request->email)->first();

            if ($userAuth && Hash::check($request->password, $userAuth->password)) {
                Log::info('AuthenticateUsing: Credentials matched', ['user_auth_id' => $userAuth->id]);

                // Added for validation of account login in company
                UserAuth::validateLoginActiveDisabled($userAuth);
                Log::info('AuthenticateUsing: Validation passed');

                session()->put([
                    'current_latitude' => $request->current_latitude,
                    'current_longitude' => $request->current_longitude,
                ]);

                return $userAuth;
            }
            Log::warning('AuthenticateUsing: Credentials failed or user not found');
        });

        Fortify::requestPasswordResetLinkView(function () {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale.'_'.mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.passwords.forget', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::loginView(function () {

            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                echo view('vendor.craveva.install_message');
                exit(1);
            }

            $this->checkMigrateStatus();
            $globalSetting = global_setting();
            // Is craveva
            $company = Company::withCount('users')->first();

            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale.'_'.mb_strtoupper($globalSetting->locale));

            $userTotal = User::count();

            if ($userTotal == 0) {
                $accountSetupBlade = 'auth.account_setup';

                if (isCraveva()) {
                    $accountSetupBlade = 'super-admin.account_setup';
                }

                return view($accountSetupBlade, ['global' => $globalSetting, 'setting' => $globalSetting]);
            }

            $socialAuthSettings = social_auth_setting();
            $languages = language_setting();
            $frontWidgets = FrontWidget::all();

            if ($globalSetting->front_design == 1 && $globalSetting->login_ui == 1 && ! module_enabled('Subdomain')) {
                $frontDetail = FrontDetail::first();

                if (session()->has('language')) {
                    $locale = session('language');
                } else {
                    $locale = $frontDetail->locale;
                }

                App::setLocale($locale);
                Carbon::setLocale($locale);
                setlocale(LC_TIME, $locale.'_'.mb_strtoupper($locale));

                $localeLanguage = LanguageSetting::where('language_code', App::getLocale())->first();

                $frontMenuCount = FrontMenu::select('id', 'language_setting_id')->where('language_setting_id', $localeLanguage?->id)->count();
                $frontMenu = FrontMenu::where('language_setting_id', $frontMenuCount > 0 ? ($localeLanguage?->id) : null)->first();
                $footerMenuCount = FooterMenu::select('id', 'language_setting_id')->where('language_setting_id', $localeLanguage?->id)->count();
                $footerSettings = FooterMenu::whereNotNull('slug')->where('language_setting_id', $footerMenuCount > 0 ? ($localeLanguage?->id) : null)->get();

                return view(
                    'super-admin.saas.login',
                    [
                        'setting' => $globalSetting,
                        'socialAuthSettings' => $socialAuthSettings,
                        'company' => $company,
                        'global' => $globalSetting,
                        'frontMenu' => $frontMenu,
                        'footerSettings' => $footerSettings,
                        'locale' => $locale,
                        'frontDetail' => $frontDetail,
                        'languages' => $languages,
                        'frontWidgets' => $frontWidgets,
                    ]
                );
            }

            return view('auth.login', [
                'globalSetting' => $globalSetting,
                'socialAuthSettings' => $socialAuthSettings,
                'company' => $company,
                'languages' => $languages,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::resetPasswordView(function ($request) {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale.'_'.mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.passwords.reset-password', [
                'request' => $request,
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::confirmPasswordView(function ($request) {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale.'_'.mb_strtoupper($globalSetting->locale));

            return view('auth.password-confirm', ['request' => $request, 'globalSetting' => $globalSetting]);
        });

        Fortify::twoFactorChallengeView(function () {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale.'_'.mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.two-factor-challenge', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::registerView(function () {

            // ISCRAVEVA
            $company = Company::first();
            $globalSetting = GlobalSetting::first();

            if (! $company->allow_client_signup) {
                return redirect(route('login'));
            }

            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale.'_'.mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.register', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::verifyEmailView(function () {
            $userAuth = UserAuth::find(user()->user_auth_id);
            $isClient = User::isClient(user()->id);

            if ($isClient) {
                $companySetting = Company::find(session('company')->id);
                $user = User::find(user()->id);

                session([
                    'isClient' => $isClient,
                    'admin_approval' => $user->admin_approval,
                    'admin_client_signup_approval' => $companySetting->admin_client_signup_approval,
                ]);
            } else {
                session([
                    'isClient' => false,
                    'admin_approval' => false,
                    'admin_client_signup_approval' => false,
                ]);
            }

            if (\App\Models\GlobalSetting::value('email_verification') == 0) {

                return redirect()->route('login');
            }

            if ((! is_null($userAuth->email_code_expires_at) && $userAuth->email_code_expires_at->isPast()) || is_null($userAuth->email_code_expires_at)) {
                $userAuth->sendEmailVerificationNotification();
            }

            $frontWidgets = FrontWidget::all();

            return view('auth.verify-email', [
                'frontWidgets' => $frontWidgets,
            ]);
        });
    }

    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }
}
