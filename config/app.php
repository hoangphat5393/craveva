<?php

use App\Providers\AppServiceProvider;
use App\Providers\CustomConfigProvider;
use App\Providers\EventServiceProvider;
use App\Providers\FileStorageCustomConfigProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\RouteServiceProvider;
use Barryvdh\TranslationManager\ManagerServiceProvider;
use Froiden\LaravelInstaller\Providers\LaravelInstallerServiceProvider;
use Froiden\RestAPI\Facades\ApiRoute;
use Froiden\RestAPI\Providers\ApiServiceProvider;
use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Auth\Passwords\PasswordResetServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Cookie\CookieServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Pipeline\PipelineServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Macellan\Zip\ZipFacade;
use Macellan\Zip\ZipServiceProvider;
use Webklex\PDFMerger\Facades\PDFMergerFacade;
use Webklex\PDFMerger\Providers\PDFMergerServiceProvider;
use Yajra\DataTables\Facades\DataTables;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    // This will determine if the application craveva or non-craveva
    'app_name' => 'craveva',

    'name' => 'Craveva',

    // We will use this for email copyright message
    'global_app_name' => 'craveva',

    'main_domain_name' => env('MAIN_DOMAIN_NAME', null),

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'app_configuration_mode' => env('APP_CONFIGURATION_MODE', 'browser'),

    'non_saas_to_saas_enabled' => env('NON_SAAS_TO_SAAS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Seeding
    |--------------------------------------------------------------------------
    | This tells if the data is seeding  (php artisan db:seed)
    |
    */

    'seeding' => false,
    'redirect_https' => env('REDIRECT_HTTPS', false),
    'seed_record_count' => env('SEED_RECORD_COUNT', 5),
    'extra_company_seed_count' => env('EXTRA_COMPANY_SEED_COUNT', 0),
    'main_application_subdomain' => env('MAIN_APPLICATION_SUBDOMAIN'),
    'short_domain_name' => env('SHORT_DOMAIN_NAME', false),

    /*
    | Import progress polling: optional queue:work inside HTTP (legacy). Staging/production should use
    | Supervisor and leave this unset — nginx/php-fpm timeouts break JSON polling otherwise.
    |
    | When IMPORT_PROGRESS_RUN_QUEUE_WORKER is unset, inline worker runs only if APP_ENV matches one of
    | these names (comma-separated). Default is local,development — if you use APP_ENV=craveva (.env.example),
    | set IMPORT_PROGRESS_RUN_QUEUE_WORKER=true or add craveva to IMPORT_PROGRESS_RUN_QUEUE_WORKER_ENVIRONMENTS.
    */
    'import_progress_run_queue_worker' => env('IMPORT_PROGRESS_RUN_QUEUE_WORKER'),
    'import_progress_execution_jobs_per_poll' => env('IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL', 8),
    /*
     * When running queue:work inside the poll request, cap worker duration (seconds) so nginx/php-fpm
     * does not kill the request before JSON returns — especially for large Client import (many chunk jobs).
     * Set to 0 to disable (not recommended behind short proxy timeouts). Default 25.
     */
    'import_progress_worker_max_seconds' => (int) env('IMPORT_PROGRESS_WORKER_MAX_SECONDS', 25),
    'import_progress_run_queue_worker_environments' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('IMPORT_PROGRESS_RUN_QUEUE_WORKER_ENVIRONMENTS', 'local,development'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    'currency_converter_key' => env('CURRENCY_CONVERTER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
        |--------------------------------------------------------------------------
        | API Debug Mode
        |--------------------------------------------------------------------------
        |
        | When your application is in debug mode, detailed error messages with
        | stack traces will be shown on every error that occurs within your
        | application. If disabled, a simple generic error page is shown.
        |
        */
    'api_debug' => env('APP_API_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),
    'main_app_url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('DB_TIMEZONE', 'UTC'),
    'cron_timezone' => env('CRON_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',
    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | AI order webhook (integrations)
    |--------------------------------------------------------------------------
    |
    | Shared secret for POST /ai-order-webhook/{hash} when no per-company secret is set.
    | Prefer generating a secret per company from Sale order settings (companies.ai_order_webhook_secret).
    | Set in .env as AI_ORDER_WEBHOOK_SECRET.
    |
    */

    'ai_order_webhook_secret' => env('AI_ORDER_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        AuthServiceProvider::class,
        BroadcastServiceProvider::class,
        BusServiceProvider::class,
        CacheServiceProvider::class,
        ConsoleSupportServiceProvider::class,
        CookieServiceProvider::class,
        DatabaseServiceProvider::class,
        EncryptionServiceProvider::class,
        FilesystemServiceProvider::class,
        FoundationServiceProvider::class,
        HashServiceProvider::class,
        NotificationServiceProvider::class,
        PaginationServiceProvider::class,
        PipelineServiceProvider::class,
        QueueServiceProvider::class,
        RedisServiceProvider::class,
        PasswordResetServiceProvider::class,
        SessionServiceProvider::class,
        TranslationServiceProvider::class,
        ValidationServiceProvider::class,
        ViewServiceProvider::class,
        MailServiceProvider::class,
        FileStorageCustomConfigProvider::class,
        CustomConfigProvider::class,
        PDFMergerServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        EventServiceProvider::class,
        RouteServiceProvider::class,
        ApiServiceProvider::class,
        FortifyServiceProvider::class,
        ManagerServiceProvider::class,
        ZipServiceProvider::class,
        LaravelInstallerServiceProvider::class,

        App\Providers\SuperAdmin\EventServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded, so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        'ApiRoute' => ApiRoute::class,
        'DataTables' => DataTables::class,
        'Zip' => ZipFacade::class,
        'PDFMerger' => PDFMergerFacade::class,
    ])->toArray(),

    'debug_blacklist' => [
        '_ENV' => [
            'APP_KEY',
            'DB_PASSWORD',
            'REDIS_PASSWORD',
            'MAIL_PASSWORD',
            'PUSHER_APP_KEY',
            'PUSHER_APP_SECRET',
            'FTP_PASSWORD',
            'RAZORPAY_SECRET',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
        ],
        '_SERVER' => [
            'APP_KEY',
            'DB_PASSWORD',
            'REDIS_PASSWORD',
            'MAIL_PASSWORD',
            'PUSHER_APP_KEY',
            'PUSHER_APP_SECRET',
            'FTP_PASSWORD',
            'RAZORPAY_SECRET',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',

        ],
        '_POST' => [
            'password',
        ],
    ],

];
