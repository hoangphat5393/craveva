<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AiWorkspaceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.aiWorkspace';

        $this->middleware(function ($request, $next) {
            abort_403(! $this->canAccessAiWorkspace());

            $scriptUrl = global_setting()->aiWorkspaceWidgetScriptUrl();
            abort_unless($scriptUrl, 404);

            return $next($request);
        });
    }

    public function index(): View
    {
        $global = global_setting();

        $this->widgetScriptUrl = $global->aiWorkspaceWidgetScriptUrl();
        $this->agentId = $global->ai_workspace_agent_id;
        $this->apiKey = $global->ai_workspace_api_key;

        return view('ai-workspace.index', $this->data);
    }

    protected function canAccessAiWorkspace(): bool
    {
        if (user()->is_superadmin) {
            return true;
        }

        return in_array('admin', array_map('strtolower', user_roles() ?? []));
    }
}
