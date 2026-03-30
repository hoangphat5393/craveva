<?php

namespace Modules\Pricing\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\ClientDetails;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Pricing\DataTables\ClientTiersDataTable;
use Modules\Pricing\Entities\PricingTier;

class ClientTierController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('pricing::app.menu.pricing');
        $this->middleware(function ($request, $next) {
            abort_403(! in_array('pricing', array_map('strtolower', $this->user->modules)));
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
        $viewPermission = user()->permission('view_client_tiers');
        abort_403($viewPermission == 'none');

        $this->client = User::with('clientDetails')->findOrFail($id);

        if (! $this->client->clientDetails) {
            $details = new ClientDetails;
            $details->user_id = $this->client->id;
            $details->company_name = $this->client->name ?? 'Client ' . $this->client->id;
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
            'pricing_tier_id' => [
                'nullable',
                'integer',
                Rule::exists('pricing_tiers', 'id')->where(function ($query) {
                    $query->where('company_id', company()->id)
                        ->orWhereNull('company_id');
                }),
            ],
        ]);

        $user = User::findOrFail($id);
        $details = $user->clientDetails;

        if (! $details) {
            $details = new ClientDetails;
            $details->user_id = $user->id;
            $details->company_name = $user->name;
            if ($user->company_id) {
                $details->company_id = $user->company_id;
            } elseif (company()) {
                $details->company_id = company()->id;
            }
        }

        // Only assign tier here. Do not overwrite client_code from this form — it caused
        // duplicate (company_id, client_code) when the hidden field drifted from the switcher.
        // Customer code is edited on the client profile / import, not on tier assignment.
        $details->pricing_tier_id = $request->pricing_tier_id;
        $details->save();

        return Reply::success(__('messages.updateSuccess'));
    }
}
