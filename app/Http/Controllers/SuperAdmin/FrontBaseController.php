<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin\FooterMenu;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\FrontMenu;
use App\Models\SuperAdmin\FrontWidget;
use App\Models\SuperAdmin\TrFrontDetail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class FrontBaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            echo view('vendor.craveva.install_message');
            exit(1);
        }
        $this->middleware(function ($request, $next) {

            $this->frontDetail = FrontDetail::first();
            $this->languages = language_setting();
            $this->global = $this->globalSetting = $this->setting = global_setting();

            $this->locale = $this->frontDetail?->locale ?? $this->global?->locale ?? config('app.locale', 'en');

            if (session()->has('language')) {
                $this->locale = session('language');
            }

            App::setLocale($this->locale);
            Carbon::setLocale($this->locale);
            setlocale(LC_TIME, $this->locale.'_'.strtoupper($this->locale));

            $this->enLocaleLanguage = language_setting_locale('en');
            $this->localeLanguage = $this->locale != 'en' ? language_setting_locale($this->locale) : $this->enLocaleLanguage;
            $this->localeLanguage = $this->localeLanguage ?: $this->enLocaleLanguage;

            $localeLanguageId = $this->localeLanguage?->id;
            $enLocaleLanguageId = $this->enLocaleLanguage?->id;

            $this->footerSettings = FooterMenu::query()
                ->whereNotNull('slug')
                ->where('private', 0)
                ->when($localeLanguageId, fn ($query) => $query->where('language_setting_id', $localeLanguageId))
                ->get();

            if ($this->footerSettings->isEmpty() && $enLocaleLanguageId) {
                $this->footerSettings = FooterMenu::whereNotNull('slug')
                    ->where('private', 0)
                    ->where('language_setting_id', $enLocaleLanguageId)
                    ->get();
            }

            $this->frontMenu = $localeLanguageId
                ? FrontMenu::where('language_setting_id', $localeLanguageId)->first()
                : null;
            $this->frontMenu = $this->frontMenu ?: ($enLocaleLanguageId
                ? FrontMenu::where('language_setting_id', $enLocaleLanguageId)->first()
                : null);

            $this->frontWidgets = FrontWidget::all();

            $this->detail = $this->frontDetail;

            $this->trFrontDetail = $localeLanguageId
                ? TrFrontDetail::where('language_setting_id', $localeLanguageId)->first()
                : null;
            $this->trFrontDetail = $this->trFrontDetail ?: ($enLocaleLanguageId
                ? TrFrontDetail::where('language_setting_id', $enLocaleLanguageId)->first()
                : null);

            // ACCOUNT SETUP REDIRECT
            $userTotal = User::count();

            if ($userTotal == 0 && ! module_enabled('Subdomain')) {
                return redirect()->route('login');
            }

            return $next($request);
        });
    }
}
