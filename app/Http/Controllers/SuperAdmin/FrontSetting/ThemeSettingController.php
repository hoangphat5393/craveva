<?php

namespace App\Http\Controllers\SuperAdmin\FrontSetting;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Http\Requests\SuperAdmin\ThemeSetting\UpdateRequest;
use App\Models\CompanyAddress;
use App\Models\GlobalSetting;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\GlobalCurrency;
use App\Models\ThemeSetting;
use App\Scopes\CompanyScope;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;

class ThemeSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'superadmin.menu.frontThemeSettings';
        $this->activeSettingMenu = 'front_theme_settings';

        $this->middleware(function ($request, $next) {
            abort_403(GlobalSetting::validateSuperAdmin('manage_superadmin_front_settings'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application
     */
    public function index()
    {
        $this->global = global_setting();
        $this->frontDetail = FrontDetail::first();
        $this->currencies = GlobalCurrency::all();
        $this->superadminTheme = ThemeSetting::where('panel', 'superadmin')->first();
        $this->companyAddresses = CompanyAddress::all();

        return view('super-admin.front-setting.theme-setting.index', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws RelatedResourceNotFoundException
     */
    public function themeUpdate(UpdateRequest $request): array
    {
        $global = global_setting();
        $global->front_design = $request->theme;
        $global->frontend_disable = $request->has('frontend_disable') ? 1 : 0;
        $global->setup_homepage = $request->setup_homepage;
        $global->custom_homepage_url = $request->custom_homepage_url;

        if ($request->has('login_ui')) {
            $global->login_ui = $request->login_ui;
        }

        $global->save();

        $adminTheme = ThemeSetting::withoutGlobalScope(CompanyScope::class)
            ->firstOrCreate(
                [
                    'panel' => 'superadmin',
                    'company_id' => null,
                ],
                [
                    'header_color' => '#ed4040',
                    'sidebar_color' => '#292929',
                    'sidebar_text_color' => '#cbcbcb',
                    'link_color' => '#ffffff',
                    'sidebar_theme' => 'dark',
                    'enable_rounded_theme' => 0,
                ]
            );

        $adminTheme->login_background = $request->logo_background_color;
        $adminTheme->enable_rounded_theme = $request->has('rounded_theme') ? 1 : 0;
        $adminTheme->save();

        $setting = FrontDetail::first();
        if ($setting === null) {
            $setting = new FrontDetail;
            $setting->get_started_show = 'yes';
            $setting->sign_in_show = 'yes';
            $setting->locale = $request->default_language ?? 'en';
            $setting->homepage_background = 'default';
            $setting->background_color = '#CDDCDC';
            $setting->save();
        }

        $setting->locale = $request->default_language;
        $setting->homepage_background = $request->homepage_background;

        if ($request->has('primary_color')) {
            $setting->primary_color = $request->primary_color;
        }

        if ($request->has('homepage_background') && $request->homepage_background != 'default') {
            $setting->background_color = $request->background_color;

            if ($request->background_image_delete == 'yes') {
                Files::deleteFile($setting->background_image, 'front/homepage-background');
                $setting->background_image = null;
                $setting->homepage_background = 'default';
            }

            if ($request->hasFile('background_image')) {
                Files::deleteFile($setting->background_image, 'front/homepage-background');
                $setting->background_image = Files::uploadLocalOrS3($request->background_image, 'front/homepage-background');
            }
        }

        $setting->save();

        cache()->forget('global_setting');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('superadmin.front-settings.front_theme_settings')]);
    }
}
