<?php

namespace Modules\Pricing\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\ClientDetails;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Pricing\DataTables\ClientTiersDataTable;
use Modules\Pricing\Entities\PricingTier;

class ClientTierController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
        $this->middleware(function ($request, $next) {
            // Ensure strict company context
            if (! company()) {
                abort(403, 'Company context is required.');
            }

            return $next($request);
        });
    }

    public function index(ClientTiersDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_client_tiers');
        abort_403($viewPermission == 'none');

        $this->tiers = PricingTier::orderBy('name')->get();

        return $dataTable->render('pricing::client_tiers.index', $this->data);
    }

    public function edit($id)
    {
        \Illuminate\Support\Facades\Log::info('ClientTierController@edit called for ID: '.$id);
        $this->client = User::with('clientDetails')->findOrFail($id);

        if (! $this->client->clientDetails) {
            $details = new ClientDetails;
            $details->user_id = $this->client->id;
            $details->company_name = $this->client->name ?? 'Client '.$this->client->id;
            // Ensure company_id is set if available on client
            if ($this->client->company_id) {
                $details->company_id = $this->client->company_id;
            }
            $details->save();
            $this->client->load('clientDetails');
        }

        $this->tiers = PricingTier::orderBy('name')->get();
        $this->clients = User::allClients(active: false);

        if (request()->ajax()) {
            return $this->returnAjax('pricing::client_tiers.ajax.edit');
        }

        return view('pricing::client_tiers.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $editPermission = user()->permission('edit_client_pricing');
        abort_403($editPermission == 'none');

        $request->validate([
            'pricing_tier_id' => 'nullable|integer',
            'client_code' => 'nullable|string|max:100',
        ]);

        $user = User::findOrFail($id);
        $details = $user->clientDetails;

        if (! $details) {
            $details = new ClientDetails;
            $details->user_id = $user->id;
            $details->company_name = $user->name;
        }

        $details->client_code = $request->client_code;
        $details->pricing_tier_id = $request->pricing_tier_id;
        $details->save();

        return Reply::success(__('messages.updateSuccess'));
    }
}
