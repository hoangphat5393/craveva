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

            abort_unless(global_setting()->hasAiWorkspaceIntegration(), 404);

            return $next($request);
        });
    }

    public function index(): View
    {
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
