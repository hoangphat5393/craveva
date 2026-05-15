<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use Carbon\Carbon;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;

class Controller extends BaseController
{
    use AppBoot, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param  mixed  $name
     * @param  mixed  $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param  mixed  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param  mixed  $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $this->checkMigrateStatus();

            // To keep the session we need to move it to middleware
            $this->gdpr = gdpr_setting();
            $this->global = global_setting();

            $this->company = companyOrGlobalSetting();

            $this->socialAuthSettings = social_auth_setting();

            $activeTenant = company();
            $hasTenantCompany = $activeTenant instanceof Company;

            // Super Admin panel: luôn dùng global_app_name cho sidebar (Theme Settings)
            // Tránh trường hợp auto-set company khiến hiển thị sai tên
            // Dùng route check vì user() có thể trả về company user (is_superadmin=0) khi session có company
            $isSuperAdminRoute = request()->routeIs('superadmin.*')
                || request()->routeIs('app-settings.*')
                || (request()->route() && str_starts_with(request()->route()->uri(), 'account/settings'));
            if ($isSuperAdminRoute && auth()->check()) {
                $this->companyName = $this->global->global_app_name;
                $this->appName = $this->global->global_app_name;
            } elseif ($hasTenantCompany) {
                // Superadmin không có company_id vẫn có thể vào tenant qua session('company');
                // companyOrGlobalSetting() lúc đó là GlobalSetting — phải lấy tên từ model Company trong session.
                $this->companyName = $activeTenant->company_name;
                $app = $activeTenant->app_name;
                $this->appName = (is_string($app) && $app !== '') ? $app : $activeTenant->company_name;
            } else {
                $this->companyName = $this->global->global_app_name;
                $this->appName = $this->global->global_app_name;
            }
            $this->locale = session('locale') ? session('locale') : ($hasTenantCompany ? $activeTenant->locale : $this->global->locale);

            $this->taskBoardColumnLength = $hasTenantCompany
                ? $activeTenant->taskboard_length
                : ($this->company?->taskboard_length ?? 10);

            config(['app.name' => $this->companyName]);
            config(['app.url' => url('/')]);

            App::setLocale($this->locale);
            Carbon::setLocale($this->locale);

            setlocale(LC_TIME, $this->locale . '_' . mb_strtoupper($this->locale));

            // config(['app.debug' => $this->global->app_debug]);

            return $next($request);
        });
    }

    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }

    public function returnAjax($view)
    {
        $html = view($view, $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
    }
}
