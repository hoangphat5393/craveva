<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Settings\UpdateOrganisationSettings;
use App\Traits\CurrencyExchange;
use Illuminate\Http\Request;

class SettingsController extends AccountBaseController
{
    use CurrencyExchange;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.accountSettings';
        $this->activeSettingMenu = 'company_settings';
        $this->middleware(function ($request, $next) {
            if (user()->is_superadmin) {
                return redirect(route('app-settings.index'));
            }

            return user()->permission('manage_company_setting') !== 'all' ? redirect()->route('profile-settings.index') : $next($request);
        })->except('changeLanguage');
    }

    public function changeLanguage(Request $request)
    {
        $setting = user();
        $setting->locale = $request->lang;
        $setting->save();

        session(['locale' => $request->lang]);
        session(['user' => $setting->fresh()]);

        if ($request->ajax()) {
            return Reply::redirect(url()->previous(), __('messages.updateSuccess'));
        }

        return redirect(url()->previous());
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('company-settings.index', $this->data);
    }

    // phpcs:ignore
    public function update(UpdateOrganisationSettings $request, $id)
    {
        $setting = \company();
        $setting->app_name = $request->company_name;
        $setting->company_name = $request->company_name;
        $setting->company_email = $request->company_email;
        $setting->company_phone = $request->company_phone;
        $setting->website = $request->website;
        $setting->save();

        // session()->forget('company');
        // return Reply::success(__('messages.updateSuccess'));

        return Reply::redirect(route('company-settings.index'), __('messages.updateSuccess'));
    }

    // Remove in v 5.2.5
    public function hideWebhookAlert()
    {
        $this->company->show_new_webhook_alert = false;
        $this->company->saveQuietly();
        session()->forget('company');

        return Reply::success('Webohook alert box has been removed permanently');
    }
}
