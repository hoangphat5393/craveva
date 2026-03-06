<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\CommonRequest;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\TaskSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.taskSettings';
        $this->activeSettingMenu = 'task_settings';
        $this->middleware(function ($request, $next) {
            abort_403(! (user()->permission('manage_task_setting') == 'all' && in_array('tasks', user_modules())));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->companyData = $this->company;
        $this->taskboardColumns = TaskboardColumn::orderBy('priority', 'asc')->get();
        $this->taskSetting = TaskSetting::first();

        $tab = request('tab');

        switch ($tab) {
            case 'status':
                $this->view = 'task-settings.ajax.status';
                $this->activeTab = 'status';
                break;
            default:
                $this->view = 'task-settings.ajax.general';
                $this->activeTab = 'general';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('task-settings.index', $this->data);
    }

    /**
     * @return array
     */
    public function store(CommonRequest $request)
    {
        $company = $this->company;

        $company->before_days = $request->before_days;
        $company->after_days = $request->after_days;
        $company->on_deadline = $request->on_deadline;
        $company->default_task_status = $request->default_task_status;
        $company->taskboard_length = $request->taskboard_length;
        $company->save();
        session()->forget('company');

        $taskSetting = TaskSetting::first();
        $taskSetting->task_category = ($request->task_category) ? 'yes' : 'no';
        $taskSetting->project = ($request->project) ? 'yes' : 'no';
        $taskSetting->start_date = ($request->start_date) ? 'yes' : 'no';
        $taskSetting->due_date = ($request->due_date) ? 'yes' : 'no';
        $taskSetting->project_required = ($request->isProjectRequired == 'yes') ? 'yes' : 'no';
        $taskSetting->assigned_to = ($request->assigned_to) ? 'yes' : 'no';
        $taskSetting->description = ($request->description) ? 'yes' : 'no';
        $taskSetting->label = ($request->label) ? 'yes' : 'no';
        $taskSetting->assigned_by = ($request->assigned_by) ? 'yes' : 'no';
        $taskSetting->status = ($request->status) ? 'yes' : 'no';
        $taskSetting->priority = ($request->priority) ? 'yes' : 'no';
        $taskSetting->make_private = ($request->make_private) ? 'yes' : 'no';
        $taskSetting->time_estimate = ($request->time_estimate) ? 'yes' : 'no';
        $taskSetting->hours_logged = ($request->hours_logged) ? 'yes' : 'no';
        $taskSetting->custom_fields = ($request->custom_fields) ? 'yes' : 'no';
        $taskSetting->copy_task_link = ($request->copy_task_link) ? 'yes' : 'no';
        $taskSetting->comments = ($request->comments) ? 'yes' : 'no';
        $taskSetting->files = ($request->files_tab) ? 'yes' : 'no';
        $taskSetting->sub_task = ($request->sub_task) ? 'yes' : 'no';
        $taskSetting->time_logs = ($request->time_logs) ? 'yes' : 'no';
        $taskSetting->notes = ($request->notes) ? 'yes' : 'no';
        $taskSetting->history = ($request->history) ? 'yes' : 'no';
        $taskSetting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function create()
    {
        return view('task-settings.create_status', $this->data);
    }

    public function edit($id)
    {
        $this->taskboardColumn = TaskboardColumn::findOrFail($id);

        return view('task-settings.edit_status', $this->data);
    }

    public function storeStatus(Request $request)
    {
        $request->validate([
            'column_name' => 'required',
            'label_color' => 'required',
        ]);

        $maxPriority = TaskboardColumn::max('priority');

        $taskboardColumn = new TaskboardColumn;
        $taskboardColumn->column_name = $request->column_name;
        $taskboardColumn->slug = Str::slug($request->column_name);
        $taskboardColumn->label_color = $request->label_color;
        $taskboardColumn->priority = ($maxPriority + 1);
        $taskboardColumn->save();

        return Reply::success(__('messages.recordSaved'));
    }

    public function statusUpdate(Request $request, $id)
    {
        $request->validate([
            'column_name' => 'required',
            'label_color' => 'required',
        ]);

        $taskboardColumn = TaskboardColumn::findOrFail($id);
        $taskboardColumn->column_name = $request->column_name;

        if ($taskboardColumn->slug != 'completed' && $taskboardColumn->slug != 'incomplete') {
            $taskboardColumn->slug = Str::slug($request->column_name);
        }

        $taskboardColumn->label_color = $request->label_color;
        $taskboardColumn->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    public function setDefault($id)
    {
        $company = $this->company;
        $company->default_task_status = $id;
        $company->save();
        session()->forget('company');

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroyStatus($id)
    {
        $taskboardColumn = TaskboardColumn::findOrFail($id);

        if ($taskboardColumn->slug == 'completed' || $taskboardColumn->slug == 'incomplete') {
            return Reply::error(__('messages.cannotDeleteStatus'));
        }

        $defaultStatus = $this->company->default_task_status;
        Task::where('board_column_id', $id)->update(['board_column_id' => $defaultStatus]);

        $taskboardColumn->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
