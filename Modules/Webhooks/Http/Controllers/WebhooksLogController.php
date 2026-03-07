<?php

namespace Modules\Webhooks\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Helper\Reply;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Webhooks\DataTables\WebhookLogsDataTable;
use Modules\Webhooks\Entities\WebhooksLog;

class WebhooksLogController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('webhooks::app.webhooks');
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('webhooks', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(WebhookLogsDataTable $dataTable)
    {
        abort_403(user()->permission('view_webhooks_logs') != 'all');

        return $dataTable->render('webhooks::webhooks-log.index', $this->data);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        abort_403(user()->permission('view_webhooks_logs') != 'all');
        $this->log = WebhooksLog::findOrFail($id);

        if (request()->ajax()) {
            $html = view('webhooks::webhooks-log.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('webhooks::webhooks-log.show', $this->data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort_403(user()->permission('view_webhooks_logs') != 'all');

        $log = WebhooksLog::findOrFail($id);
        $log->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Apply quick action (bulk delete).
     *
     * @return \Illuminate\Http\Response
     */
    public function applyQuickAction(Request $request)
    {
        abort_403(user()->permission('view_webhooks_logs') != 'all');

        if ($request->action_type !== 'delete') {
            return Reply::error(__('messages.selectAction'));
        }

        $ids = explode(',', $request->row_ids);
        WebhooksLog::whereIn('id', $ids)->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
