<?php

namespace App\Http;

use App\Http\Middleware\AdminOrSuperAdmin;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateAiOrderIntegration;
use App\Http\Middleware\AutoLogout;
use App\Http\Middleware\CheckCompanyPackage;
use App\Http\Middleware\DisableFrontend;
use App\Http\Middleware\EmailVerified;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureAiOrderIntegrationMethodAllowed;
use App\Http\Middleware\EnsureTranslationToken;
use App\Http\Middleware\MultiCompanySelect;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SuperAdmin;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        TrustProxies::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            StartSession::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            ThrottleRequests::class.':web',
        ],

        'api' => [
            EnsureFrontendRequestsAreStateful::class,
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases (Laravel 11 — thay cho $routeMiddleware).
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'email_verified' => EmailVerified::class,

        'super-admin' => SuperAdmin::class,
        'multi-company-select' => MultiCompanySelect::class,
        'disable-frontend' => DisableFrontend::class,
        'admin-or-super-admin' => AdminOrSuperAdmin::class,
        'translation' => EnsureTranslationToken::class,
        'check-company-package' => CheckCompanyPackage::class,
        'auto-logout' => AutoLogout::class,
        'ai.integration.auth' => AuthenticateAiOrderIntegration::class,
        'ai.integration.method' => EnsureAiOrderIntegrationMethodAllowed::class,
    ];
}
