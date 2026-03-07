<?php

namespace Modules\Webhooks\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Webhooks\DataTables\WebhookDataTable;
use Modules\Webhooks\Entities\WebhooksRequest;
use Modules\Webhooks\Entities\WebhooksSetting;
use Modules\Webhooks\Enums\ClientVariable;
use Modules\Webhooks\Enums\EmployeeVariable;
use Modules\Webhooks\Enums\InvoiceVariable;
use Modules\Webhooks\Enums\LeadVariable;
use Modules\Webhooks\Enums\ProjectVariable;
use Modules\Webhooks\Enums\ProposalVariable;
use Modules\Webhooks\Enums\TaskVariable;
use Modules\Webhooks\Http\Requests\StoreWebhookRequest;

class WebhooksController extends AccountBaseController
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
    public function index(WebhookDataTable $dataTable)
    {
        abort_403(user()->permission('view_webhooks') != 'all');

        return $dataTable->render('webhooks::webhooks.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        abort_403(user()->permission('add_webhooks') != 'all');
        $this->pageTitle = __('webhooks::app.addWebhook');

        if (request()->ajax()) {
            $html = view('webhooks::webhooks.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'webhooks::webhooks.ajax.create';

        return view('webhooks::webhooks.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(StoreWebhookRequest $request)
    {
        abort_403(user()->permission('add_webhooks') != 'all');
        DB::beginTransaction();

        $webhook = new WebhooksSetting;
        $webhook->name = $request->name;
        $webhook->company_id = $this->company->id;
        $webhook->webhook_for = $request->webhook_for;
        $webhook->action = (int) $request->webhook_action;
        $webhook->url = $request->request_url;
        $webhook->request_method = $request->request_method;
        $webhook->request_format = $request->request_format;
        $webhook->save();

        if (isset($request->headers_name)) {

            foreach ($request->headers_name as $key => $header) {
                $headers = new WebhooksRequest;
                $headers->company_id = $this->company->id;
                $headers->webhooks_setting_id = $webhook->id;
                $headers->headers_key = $header;
                $headers->headers_value = $request->headers_value[$key];
                $headers->request_type = 'headers';

                if ($headers->headers_key != '' && $headers->headers_value != '') {
                    $headers->save();
                }
            }
        }

        if (isset($request->body_key)) {

            foreach ($request->body_key as $key => $data) {
                $body = new WebhooksRequest;
                $body->company_id = $this->company->id;
                $body->webhooks_setting_id = $webhook->id;
                $body->request_type = 'body';
                $body->body_key = $data;
                $body->body_value = $request->body_value[$key];

                if ($body->body_key != '' && $body->body_value != '') {
                    $body->save();
                }
            }
        }

        DB::commit();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        abort_403(user()->permission('edit_webhooks') != 'all');
        $this->pageTitle = __('webhooks::app.editWebhook');

        $this->webhook = WebhooksSetting::with(['webhooksHeadersRequests', 'webhooksBodyRequests'])->findOrFail($id);

        if (request()->ajax()) {
            $html = view('webhooks::webhooks.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'webhooks::webhooks.ajax.edit';

        return view('webhooks::webhooks.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(StoreWebhookRequest $request, $id)
    {
        abort_403(user()->permission('edit_webhooks') != 'all');
        DB::beginTransaction();

        $webhook = WebhooksSetting::findOrFail($id);
        $webhook->name = $request->name;
        $webhook->company_id = $this->company->id;
        $webhook->webhook_for = $request->webhook_for;
        $webhook->action = (int) $request->webhook_action;
        $webhook->url = $request->request_url;
        $webhook->request_method = $request->request_method;
        $webhook->request_format = $request->request_format;
        $webhook->save();

        $webhook->webhooksRequests()->delete();

        if (isset($request->headers_name)) {

            foreach ($request->headers_name as $key => $header) {
                $headers = new WebhooksRequest;
                $headers->company_id = $this->company->id;
                $headers->webhooks_setting_id = $webhook->id;
                $headers->headers_key = $header;
                $headers->headers_value = $request->headers_value[$key];
                $headers->request_type = 'headers';

                if ($headers->headers_key != '' && $headers->headers_value != '') {
                    $headers->save();
                }
            }
        }

        if (isset($request->body_key)) {

            foreach ($request->body_key as $key => $data) {
                $body = new WebhooksRequest;
                $body->company_id = $this->company->id;
                $body->webhooks_setting_id = $webhook->id;
                $body->request_type = 'body';
                $body->body_key = $data;
                $body->body_value = $request->body_value[$key];

                if ($body->body_key != '' && $body->body_value != '') {
                    $body->save();
                }
            }
        }

        DB::commit();

        return Reply::success(__('messages.updatedSucces'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        abort_403(user()->permission('delete_webhooks') != 'all');
        $webhook = WebhooksSetting::findOrFail($id);

        if (! $webhook) {
            return Reply::error(__('webhooks::messages.webhookNotFound'));
        }

        // Delete webhook and associated requests
        $webhook->webhooksRequests()->delete();
        $webhook->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('webhooks.index')]);
    }

    /**
     * Duplicate the specified webhook.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate($id)
    {
        abort_403(user()->permission('add_webhooks') != 'all');

        $source = WebhooksSetting::with(['webhooksHeadersRequests', 'webhooksBodyRequests'])->findOrFail($id);

        DB::beginTransaction();

        $webhook = new WebhooksSetting;
        $webhook->name = $source->name . ' (' . __('app.copy') . ')';
        $webhook->company_id = $this->company->id;
        $webhook->webhook_for = $source->webhook_for;
        $webhook->action = $source->action;
        $webhook->url = $source->url;
        $webhook->request_method = $source->request_method;
        $webhook->request_format = $source->request_format;
        $webhook->status = $source->status ?? 'active';
        $webhook->save();

        foreach ($source->webhooksHeadersRequests as $header) {
            $newHeader = new WebhooksRequest;
            $newHeader->company_id = $this->company->id;
            $newHeader->webhooks_setting_id = $webhook->id;
            $newHeader->headers_key = $header->headers_key;
            $newHeader->headers_value = $header->headers_value;
            $newHeader->request_type = 'headers';
            $newHeader->save();
        }

        foreach ($source->webhooksBodyRequests as $body) {
            $newBody = new WebhooksRequest;
            $newBody->company_id = $this->company->id;
            $newBody->webhooks_setting_id = $webhook->id;
            $newBody->request_type = 'body';
            $newBody->body_key = $body->body_key;
            $newBody->body_value = $body->body_value;
            $newBody->save();
        }

        DB::commit();

        return Reply::success(__('messages.recordSaved'));
    }

    public function applyQuickAction(Request $request)
    {
        abort_403(user()->permission('edit_webhooks') != 'all' && user()->permission('delete_webhooks') != 'all');

        // Single row (status/webhook_for dropdown in table)
        if ($request->has('id') && $request->has('type')) {
            if ($request->type == 'status') {
                abort_403(user()->permission('edit_webhooks') != 'all');
                WebhooksSetting::where('id', $request->id)->update(['status' => $request->status]);

                return Reply::success(__('messages.updateSuccess'));
            }
            if ($request->type == 'webhook_for') {
                abort_403(user()->permission('edit_webhooks') != 'all');
                WebhooksSetting::where('id', $request->id)->update(['webhook_for' => $request->webhook_for]);

                return Reply::success(__('messages.updateSuccess'));
            }
            if ($request->type == 'debug') {
                WebhooksSetting::where('id', $request->id)->update(['run_debug' => $request->status]);

                return Reply::success(__('messages.updateSuccess'));
            }

            return Reply::error(__('messages.selectAction'));
        }

        // Bulk action
        $ids = array_filter(explode(',', $request->row_ids ?? ''));

        if (empty($ids)) {
            return Reply::error(__('messages.selectAction'));
        }

        switch ($request->action_type) {
            case 'change-status':
                abort_403(user()->permission('edit_webhooks') != 'all');
                WebhooksSetting::whereIn('id', $ids)->update(['status' => $request->status]);

                return Reply::success(__('messages.updateSuccess'));
            case 'delete':
                abort_403(user()->permission('delete_webhooks') != 'all');
                $webhooks = WebhooksSetting::whereIn('id', $ids)->get();
                foreach ($webhooks as $webhook) {
                    $webhook->webhooksRequests()->delete();
                    $webhook->delete();
                }

                return Reply::success(__('messages.deleteSuccess'));
            default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    public function webhooksForVariable($webhookFor)
    {
        $options = match ($webhookFor) {
            'Client' => ClientVariable::cases(),
            'Employee' => EmployeeVariable::cases(),
            'Invoice' => InvoiceVariable::cases(),
            'Lead' => LeadVariable::cases(),
            'Project' => ProjectVariable::cases(),
            'Proposal' => ProposalVariable::cases(),
            'Task' => TaskVariable::cases(),
            default => [],
        };

        return Reply::dataOnly(['options' => $options]);
    }
}
