<?php

namespace App\Models;

use App\Models\SuperAdmin\GlobalInvoice;
use App\Models\SuperAdmin\Package;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use App\Traits\CustomFieldsTrait;
use App\Traits\HasMaskImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Laravel\Cashier\Billable;

/**
 * App\Models\Company
 *
 * @property int $id
 * @property string $company_name
 * @property string $app_name
 * @property string $company_email
 * @property string $company_phone
 * @property string|null $logo
 * @property string|null $login_background
 * @property string $address
 * @property string|null $website
 * @property int|null $currency_id
 * @property string $timezone
 * @property string $date_format
 * @property string|null $date_picker_format
 * @property string|null $moment_format
 * @property string $time_format
 * @property string $locale
 * @property string $latitude
 * @property string $longitude
 * @property string $leaves_start_from
 * @property string $active_theme
 * @property int|null $last_updated_by
 * @property string|null $currency_converter_key
 * @property string|null $google_map_key
 * @property string $task_self
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $purchase_code
 * @property string|null $supported_until
 * @property string $google_recaptcha_status
 * @property string $google_recaptcha_v2_status
 * @property string|null $google_recaptcha_v2_site_key
 * @property string|null $google_recaptcha_v2_secret_key
 * @property string $google_recaptcha_v3_status
 * @property string|null $google_recaptcha_v3_site_key
 * @property string|null $google_recaptcha_v3_secret_key
 * @property int $app_debug
 * @property int $rounded_theme
 * @property int $system_update
 * @property string $logo_background_color
 * @property int $before_days
 * @property int $after_days
 * @property string $on_deadline
 * @property int $default_task_status
 * @property int $show_review_modal
 * @property int $dashboard_clock
 * @property int $taskboard_length
 * @property string|null $favicon
 * @property-read Currency|null $currency
 * @property-read mixed $dark_logo_url
 * @property-read mixed $favicon_url
 * @property-read mixed $icon
 * @property-read mixed $light_logo_url
 * @property-read mixed $masked_default_logo
 * @property-read mixed $login_background_url
 * @property-read mixed $logo_url
 * @property-read mixed $moment_date_format
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereActiveTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAfterDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAppDebug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereBeforeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCompanyPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyConverterKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDashboardClock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDatePickerFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereDefaultTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleMapKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV2Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3SiteKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleRecaptchaV3Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereHideCronMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeavesStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLoginBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLogoBackgroundColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereMomentFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereOnDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting wherePurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereRoundedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereShowReviewModal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSystemUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskSelf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTaskboardLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimeFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWeatherKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereWebsite($value)
 *
 * @property int $ticket_form_google_captcha
 * @property int $lead_form_google_captcha
 * @property string|null $last_cron_run
 * @property string $auth_theme
 * @property string|null $light_logo
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAuthTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLastCronRun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLeadFormGoogleCaptcha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereLightLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereTicketFormGoogleCaptcha($value)
 *
 * @property string $sidebar_logo_style
 * @property string $session_driver
 * @property int $allow_client_signup
 * @property int $admin_client_signup_approval
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAdminClientSignupApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowClientSignup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSessionDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSidebarLogoStyle($value)
 *
 * @property string $google_calendar_status
 * @property string|null $google_client_id
 * @property string|null $google_client_secret
 * @property string $google_calendar_verification_status
 * @property string|null $google_id
 * @property string|null $name
 * @property string|null $token
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleCalendarVerificationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereAllowedFileSize($value)
 *
 * @property string $status
 * @property string|null $last_login
 * @property int $rtl
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereStatus($value)
 *
 * @property-read AttendanceSetting|null $attendanceSetting
 * @property-read Collection|CompanyAddress[] $companyAddress
 * @property-read int|null $company_address_count
 * @property-read InvoiceSetting|null $invoiceSetting
 * @property-read Collection|LeadAgent[] $leadAgents
 * @property-read int|null $lead_agents_count
 * @property-read Collection|LeadCategory[] $leadCategories
 * @property-read int|null $lead_categories_count
 * @property-read Collection|LeadSource[] $leadSources
 * @property-read int|null $lead_sources_count
 * @property-read Collection|LeadStatus[] $leadStats
 * @property-read int|null $lead_stats_count
 * @property-read Collection|LeaveType[] $leaveTypes
 * @property-read int|null $leave_types_count
 * @property-read MessageSetting|null $messageSetting
 * @property-read Collection|OfflinePaymentMethod[] $offlinePaymentMethod
 * @property-read int|null $offline_payment_method_count
 * @property-read PaymentGatewayCredentials|null $paymentGatewayCredentials
 * @property-read ProjectSetting|null $projectSetting
 * @property-read Collection|ProjectStatusSetting[] $projectStatusSettings
 * @property-read int|null $project_status_settings_count
 * @property-read TaskSetting|null $taskSetting
 * @property-read Collection|Tax[] $taxes
 * @property-read int|null $taxes_count
 * @property-read Collection|TicketChannel[] $ticketChannels
 * @property-read int|null $ticket_channels_count
 * @property-read Collection|TicketType[] $ticketTypes
 * @property-read int|null $ticket_types_count
 * @property-read ProjectTimeLog|null $timeLogSetting
 * @property string|null $hash
 * @property-read LeaveSetting|null $leaveSetting
 * @property-read Collection|ModuleSetting[] $moduleSetting
 * @property-read int|null $module_setting_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereHash($value)
 *
 * @property int|null $package_id
 * @property string $package_type
 * @property string|null $stripe_id
 * @property string|null $card_brand
 * @property string|null $card_last_four
 * @property string|null $trial_ends_at
 * @property string|null $licence_expire_on
 * @property-read Collection|User[] $clients
 * @property-read int|null $clients_count
 * @property-read Collection|Contract[] $contracts
 * @property-read int|null $contracts_count
 * @property-read Collection|Currency[] $currencies
 * @property-read int|null $currencies_count
 * @property-read CompanyAddress|null $defaultAddress
 * @property-read Collection|User[] $employees
 * @property-read int|null $employees_count
 * @property-read Collection|Estimate[] $estimates
 * @property-read int|null $estimates_count
 * @property-read Collection|FileStorage[] $fileStorage
 * @property-read int|null $file_storage_count
 * @property-read mixed $extras
 * @property-read Collection|Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read Collection|Lead[] $leads
 * @property-read int|null $leads_count
 * @property-read Collection|Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Package|null $package
 * @property-read Collection|Project[] $projects
 * @property-read int|null $projects_count
 * @property-read SlackSetting|null $slackSetting
 * @property-read Collection|Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read Collection|Ticket[] $tickets
 * @property-read int|null $tickets_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereYearStartsFrom($value)
 *
 * @property string $header_color
 * @property int $datatable_row_limit
 * @property int $show_new_webhook_alert
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property-read CompanyAddress|null $defaultAddress
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereDatatableRowLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereShowNewWebhookAlert($value)
 *
 * @property string $auth_theme_text
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAuthThemeText($value)
 *
 * @property int $employee_can_export_data
 * @property string|null $ai_order_webhook_secret
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereEmployeeCanExportData($value)
 *
 * @mixin \Eloquent
 *
 * @property-read User|null $user
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCardLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLicenceExpireOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePackageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereTrialEndsAt($value)
 */
class Company extends BaseModel
{
    use HasFactory;
    use HasMaskImage;

    const CUSTOM_FIELD_MODEL = 'App\Models\Company';

    use Billable, CustomFieldsTrait;

    protected $with = [];

    protected $table = 'companies';

    public $dates = [
        'last_login',
        'subscription_updated_at',
        'licence_expire_on',
    ];

    protected $casts = [
        'google_calendar_status' => 'string',
    ];

    protected $appends = [
        'logo_url',
        'login_background_url',
        'moment_date_format',
        'favicon_url',
    ];

    const DATE_FORMATS = GlobalSetting::DATE_FORMATS;

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function globalInvoices()
    {
        return $this->hasMany(GlobalInvoice::class, 'company_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class)->withoutGlobalScopes([CompanyScope::class, ActiveScope::class])->setEagerLoads([]);
    }

    public static function firstActiveAdmin($company)
    {
        $admins = Role::withoutGlobalScope(CompanyScope::class)
            ->with(['users' => function ($query) {
                $query->withoutGlobalScope(CompanyScope::class);
            }])
            ->where('name', 'admin')
            ->where('company_id', $company->id)
            ->first();

        return $admins ? $admins->users->first() : null;
    }

    public function employees()
    {
        return $this->hasMany(User::class)->whereHas('employeeDetail');
    }

    public function getLogoUrlAttribute()
    {
        if (user()) {
            if (user()->dark_theme) {
                return $this->defaultLogo();
            }
        }

        if (company() && company()->auth_theme == 'dark') {
            return $this->defaultLogo();
        }

        if (is_null($this->light_logo)) {
            return global_setting()?->light_logo_url ?? asset('img/craveva-logo.png');
        }

        return asset_url_local_s3('app-logo/'.$this->light_logo);
    }

    public function defaultLogo()
    {
        if (is_null($this->logo)) {
            return global_setting()?->dark_logo_url ?? asset('img/craveva-logo.png');
        }

        return asset_url_local_s3('app-logo/'.$this->logo);
    }

    public function getLightLogoUrlAttribute()
    {
        if (is_null($this->light_logo)) {
            return global_setting()?->light_logo_url ?? asset('img/craveva-logo.png');
        }

        return asset_url_local_s3('app-logo/'.$this->light_logo);
    }

    public function getDarkLogoUrlAttribute()
    {

        if (is_null($this->logo)) {
            return asset('img/craveva-logo.png');
        }

        return asset_url_local_s3('app-logo/'.$this->logo);
    }

    public function getLoginBackgroundUrlAttribute()
    {

        if (is_null($this->login_background) || $this->login_background == 'login-background.jpg') {
            return null;
        }

        return asset_url_local_s3('login-background/'.$this->login_background);
    }

    public function maskedDefaultLogo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->logo)) {
                    return global_setting()?->dark_logo_url ?? asset('img/craveva-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/'.$this->logo);
            },
        );
    }

    public function maskedLogoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (user()) {
                    if (user()->dark_theme) {
                        return $this->masked_default_logo;
                    }
                }

                if (company() && company()->auth_theme == 'dark') {
                    return $this->masked_default_logo;
                }

                if (is_null($this->light_logo)) {
                    return global_setting()?->light_logo_url ?? asset('img/craveva-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/'.$this->light_logo);
            },
        );
    }

    public function maskedLightLogoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->light_logo)) {
                    return global_setting()?->light_logo_url ?? asset('img/craveva-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/'.$this->light_logo);
            },
        );
    }

    public function maskedDarkLogoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->logo)) {
                    return asset('img/craveva-logo.png');
                }

                return $this->generateMaskedImageAppUrl('app-logo/'.$this->logo);
            },
        );
    }

    public function maskedLoginBackgroundUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->login_background) || $this->login_background == 'login-background.jpg') {
                    return null;
                }

                return $this->generateMaskedImageAppUrl('login-background/'.$this->login_background);
            },
        );
    }

    public function maskedFaviconUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->favicon)) {
                    return global_setting()?->favicon_url ?? asset('favicon.png');
                }

                return $this->generateMaskedImageAppUrl('favicon/'.$this->favicon);
            },
        );
    }

    public function getMomentDateFormatAttribute()
    {
        return array_key_exists($this->date_format, self::DATE_FORMATS)
            ? self::DATE_FORMATS[$this->date_format]
            : null;

        // return isset($this->date_format) ? self::DATE_FORMATS[$this->date_format] : null;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('companies.status', 'active');
    }

    public function getFaviconUrlAttribute()
    {
        if (is_null($this->favicon)) {
            return global_setting()?->favicon_url ?? asset('favicon.png');
        }

        return asset_url_local_s3('favicon/'.$this->favicon);
    }

    public function paymentGatewayCredentials(): HasOne
    {
        return $this->hasOne(PaymentGatewayCredentials::class);
    }

    public function invoiceSetting(): HasOne
    {
        return $this->hasOne(InvoiceSetting::class);
    }

    public function offlinePaymentMethod(): HasMany
    {
        return $this->hasMany(OfflinePaymentMethod::class);
    }

    public function leaveTypes()
    {
        return $this->hasMany(LeaveType::class);
    }

    public function companyAddress(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(CompanyAddress::class)->where('is_default', 1);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function ticketChannels(): HasMany
    {
        return $this->hasMany(TicketChannel::class);
    }

    public function projectSetting(): HasOne
    {
        return $this->hasOne(ProjectSetting::class);
    }

    public function projectStatusSettings(): HasMany
    {
        return $this->HasMany(ProjectStatusSetting::class);
    }

    public function attendanceSetting(): HasOne
    {
        return $this->HasOne(AttendanceSetting::class);
    }

    public function messageSetting(): HasOne
    {
        return $this->HasOne(MessageSetting::class);
    }

    public function leadSources(): HasMany
    {
        return $this->HasMany(LeadSource::class);
    }

    public function leadStats(): HasMany
    {
        return $this->HasMany(LeadStatus::class);
    }

    public function leadAgents(): HasMany
    {
        return $this->HasMany(LeadAgent::class);
    }

    public function leadCategories(): HasMany
    {
        return $this->HasMany(LeadCategory::class);
    }

    public function moduleSetting(): HasMany
    {
        return $this->HasMany(ModuleSetting::class);
    }

    public function currencies(): HasMany
    {
        return $this->HasMany(Currency::class);
    }

    public function timeLogSetting(): HasOne
    {
        return $this->HasOne(ProjectTimeLog::class);
    }

    public function taskSetting(): HasOne
    {
        return $this->HasOne(TaskSetting::class);
    }

    public function leaveSetting(): HasOne
    {
        return $this->HasOne(LeaveSetting::class);
    }

    public function slackSetting(): HasOne
    {
        return $this->HasOne(SlackSetting::class);
    }

    public function fileStorage(): HasMany
    {
        return $this->hasMany(FileStorage::class);
    }

    public static function renameOrganisationTableToCompanyTable()
    {
        if (Schema::hasTable('organisation_settings')) {
            Schema::rename('organisation_settings', 'companies');
        }
    }

    public function clients(): HasMany
    {
        return $this->hasMany(User::class)->whereHas('ClientDetails');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function users()
    {
        return $this->hasMany(User::class)->withoutGlobalScope(CompanyScope::class)->withoutGlobalScope('active');
    }

    public function approvalBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
