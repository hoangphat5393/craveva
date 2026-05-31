<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\Concerns\UpdatesGlobalAiAgentSettings;
use App\Http\Requests\Admin\App\UpdateAiAssistantWidgetSetting;
use App\Http\Requests\Admin\App\UpdateAiWorkspaceSetting;
use App\Models\GlobalSetting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CravevaAiSettingController extends AccountBaseController
{
    use UpdatesGlobalAiAgentSettings;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.cravevaAi';
        $this->activeSettingMenu = 'craveva_ai_settings';

        $this->middleware(function ($request, $next) {
            abort_403(GlobalSetting::validateSuperAdmin('manage_superadmin_app_settings'));

            return $next($request);
        });
    }

    /**
     * @return Application|Factory|View|RedirectResponse
     */
    public function index()
    {
        $tab = request('tab', 'ai-workspace-setting');

        if (in_array($tab, ['ai-workspace-setting', 'ai-assistant-widget-setting'], true) === false) {
            $tab = 'ai-workspace-setting';
        }

        $this->activeSettingMenu = 'craveva_ai_settings';

        $this->view = match ($tab) {
            'ai-assistant-widget-setting' => 'app-settings.ajax.ai-assistant-widget-setting',
            default => 'app-settings.ajax.ai-workspace-setting',
        };

        $this->activeTab = $tab;
        $this->globalSetting = GlobalSetting::first();

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => $this->pageTitle,
                'activeTab' => $this->activeTab,
            ]);
        }

        return view('craveva-ai-settings.index', $this->data);
    }

    /**
     * @return array
     */
    public function update(Request $request, int $id)
    {
        $tab = request('page', 'ai-workspace-setting');

        match ($tab) {
            'ai-workspace-setting' => $request->validate((new UpdateAiWorkspaceSetting)->rules()),
            'ai-assistant-widget-setting' => $request->validate((new UpdateAiAssistantWidgetSetting)->rules()),
            default => abort(404),
        };

        match ($tab) {
            'ai-workspace-setting' => $this->updateAiWorkspaceSetting($request),
            'ai-assistant-widget-setting' => $this->updateAiAssistantWidgetSetting($request),
            default => null,
        };

        session()->forget('company');
        cache()->forget('global_setting');
        session()->forget('companyOrGlobalSetting');

        return Reply::success(__('messages.updateSuccess'));
    }
}
