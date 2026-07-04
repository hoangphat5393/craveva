<?php

use App\DataTables\ClientsDataTable;
use App\Imports\ClientImport;
use App\Jobs\ImportClientChunkJob;
use App\Models\ClientCategory;
use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAuth;
use App\Services\ClientImportProcessor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Pricing\Entities\ClientProductPricing;
use Modules\Pricing\Entities\PricingTier;

uses(DatabaseTransactions::class);

/**
 * Resolve an active company staff user and authenticate (session user + web guard).
 */
function authenticateActiveCompanyAdmin(): ?User
{
    $user = User::query()
        ->join('role_user', 'role_user.user_id', '=', 'users.id')
        ->join('roles', 'roles.id', '=', 'role_user.role_id')
        ->where('roles.name', 'admin')
        ->where('users.status', 'active')
        ->where('users.is_superadmin', 0)
        ->whereNotNull('users.company_id')
        ->select('users.*')
        ->first();

    if (! $user) {
        return null;
    }

    $auth = $user->user_auth_id
        ? UserAuth::find($user->user_auth_id)
        : UserAuth::find($user->id);

    if (! $auth) {
        return null;
    }

    test()->actingAs($auth, 'web');
    session(['user' => $user]);

    return $user;
}

it('defines client import column map with name and client_code', function () {
    $ids = collect(ClientImport::fields())->pluck('id')->all();

    expect($ids)->toContain('name')
        ->and($ids)->toContain('client_code');
});

it('keeps client import custom only fields dynamic', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $baseIds = collect(ClientImport::fields())->pluck('id');

    expect($baseIds)->toContain('payment_terms')
        ->and($baseIds)->toContain('customer_grade')
        ->and($baseIds)->toContain('channel_type')
        ->and($baseIds)->toContain('business_type')
        ->and($baseIds)->toContain('business_closure_date')
        ->and($baseIds)->not->toContain('salesperson')
        ->and($baseIds)->not->toContain('department')
        ->and($baseIds)->not->toContain('sales_assistant_name')
        ->and($baseIds)->not->toContain('last_transaction_at');

    $group = CustomFieldGroup::query()->firstOrCreate(
        [
            'name' => 'Client',
            'model' => ClientDetails::CUSTOM_FIELD_MODEL,
            'company_id' => $actor->company_id,
        ]
    );

    CustomField::query()->updateOrCreate(
        [
            'custom_field_group_id' => $group->id,
            'name' => 'salesperson',
        ],
        [
            'company_id' => $actor->company_id,
            'label' => 'Salesperson',
            'type' => 'text',
            'required' => 'no',
            'export' => 1,
            'visible' => 'false',
        ]
    );

    $mergedIds = collect(ClientImport::mergeDynamicColumns(ClientImport::fields()))->pluck('id');

    expect($mergedIds)->toContain('salesperson');
});

it('creates a client from an import row via ClientImportProcessor', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $company = Company::find($actor->company_id);
    expect($company)->not->toBeNull();

    $suffix = uniqid('', true);
    $columns = [0 => 'name', 1 => 'client_code', 2 => 'email'];
    $row = ["Import Client {$suffix}", "IMP-{$suffix}", "import.client.{$suffix}@example.test"];

    $created = ClientImportProcessor::processRow($row, $columns, $company);

    expect($created)->toBeInstanceOf(User::class);
    expect($created->name)->toBe("Import Client {$suffix}");
    expect($created->clientDetails)->not->toBeNull();
    expect($created->clientDetails->client_code)->toBe("IMP-{$suffix}");
});

it('updates an existing client when import row matches client_code', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $company = Company::find($actor->company_id);
    $suffix = uniqid('', true);
    $columns = [0 => 'name', 1 => 'client_code', 2 => 'email'];
    $code = "IMP-UPD-{$suffix}";

    ClientImportProcessor::processRow(
        ["First Name {$suffix}", $code, "first.{$suffix}@example.test"],
        $columns,
        $company
    );

    $updated = ClientImportProcessor::processRow(
        ["Second Name {$suffix}", $code, "second.{$suffix}@example.test"],
        $columns,
        $company
    );

    expect($updated->name)->toBe("Second Name {$suffix}");
    expect($updated->clientDetails->client_code)->toBe($code);
    expect(User::where('company_id', $company->id)->whereHas('clientDetails', fn ($q) => $q->where('client_code', $code))->count())->toBe(1);
});

it('dispatches import chunk job and persists client data', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $company = Company::find($actor->company_id);
    $suffix = uniqid('', true);
    $columns = [0 => 'name', 1 => 'client_code'];
    $row = ["Chunk Client {$suffix}", "CHK-{$suffix}"];

    (new ImportClientChunkJob([$row], $columns, $company, 0))->handle();

    $user = User::where('company_id', $company->id)
        ->whereHas('clientDetails', fn ($q) => $q->where('client_code', "CHK-{$suffix}"))
        ->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe("Chunk Client {$suffix}");
});

it('returns successful ajax response for client import screen', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $add = user()->permission('add_clients');
    if (! in_array($add, ['all', 'added', 'both'], true)) {
        test()->markTestSkipped('User lacks add_clients permission.');
    }

    $response = test()->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->get(route('clients.import'));

    $response->assertSuccessful();
});

it('validates required import file on clients import store', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $add = user()->permission('add_clients');
    if (! in_array($add, ['all', 'added', 'both'], true)) {
        test()->markTestSkipped('User lacks add_clients permission.');
    }

    test()->postJson(route('clients.import.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['import_file']);
});

it('registers client import and import log routes', function () {
    expect(Route::has('clients.import'))->toBeTrue()
        ->and(Route::has('clients.import.store'))->toBeTrue()
        ->and(Route::has('clients.import.process'))->toBeTrue()
        ->and(Route::has('clients.import_log.index'))->toBeTrue()
        ->and(Route::has('clients.import_log.show'))->toBeTrue();
});

it('adds excel export button to clients datatable html when canDataTableExport is true', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    if (! canDataTableExport()) {
        test()->markTestSkipped('DataTable export disabled for this user or company.');
    }

    $reflection = new ReflectionClass(ClientsDataTable::class);
    $method = $reflection->getMethod('html');
    $source = file_get_contents($method->getFileName());

    expect($source)->toContain("'extend' => 'excel'");
});

it('versions saved client listing column state', function () {
    $reflection = new ReflectionClass(ClientsDataTable::class);
    $method = $reflection->getMethod('html');
    $source = file_get_contents($method->getFileName());

    expect($source)
        ->toContain('clientListColumnsVersion')
        ->toContain('stateSaveParams')
        ->toContain('stateLoadParams');
});

it('uses PM approved default visibility for client listing columns', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $group = CustomFieldGroup::query()
        ->where('model', ClientDetails::CUSTOM_FIELD_MODEL)
        ->first();

    if (! $group) {
        $group = new CustomFieldGroup;
        $group->name = 'Client';
        $group->model = ClientDetails::CUSTOM_FIELD_MODEL;
        $group->company_id = $actor->company_id;
        $group->save();
    }

    foreach ([
        'salesperson' => ['Salesperson', 'false'],
        'last_transaction_at' => ['Last Transaction', 'false'],
        'sales_assistant_name' => ['Sales Assistant', 'true'],
        'geographical-distinction' => ['Geographical Distinction', 'true'],
        'payment_terms' => ['Payment Terms', 'true'],
    ] as $name => [$label, $visible]) {
        CustomField::query()->updateOrCreate(
            [
                'custom_field_group_id' => $group->id,
                'name' => $name,
            ],
            [
                'company_id' => $actor->company_id,
                'label' => $label,
                'type' => 'text',
                'required' => 'no',
                'export' => 1,
                'visible' => $visible,
            ]
        );
    }

    $reflection = new ReflectionClass(ClientsDataTable::class);
    $method = $reflection->getMethod('getColumns');
    $method->setAccessible(true);
    $columns = collect($method->invoke(new ClientsDataTable))->keyBy('data');

    expect($columns->get('email')['visible'])->toBeFalse()
        ->and($columns->get('mobile')['visible'])->toBeFalse()
        ->and($columns->get('category_name')['visible'])->toBeFalse()
        ->and($columns->get('created_at')['visible'])->toBeFalse()
        ->and($columns->get('pricing_tier_name')['visible'] ?? true)->toBeTrue()
        ->and($columns->get('contract_pricing_active')['visible'] ?? true)->toBeTrue()
        ->and($columns->get('outstanding_balance')['visible'] ?? true)->toBeFalse()
        ->and($columns->get('salesperson')['visible'])->toBeTrue()
        ->and($columns->get('last_transaction_at')['visible'])->toBeTrue()
        ->and($columns->get('sales_assistant_name')['visible'])->toBeFalse()
        ->and($columns->get('geographical-distinction')['visible'])->toBeFalse()
        ->and($columns->get('payment_terms')['visible'])->toBeFalse();
});

it('renders read only client status as badges', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $reflection = new ReflectionClass(ClientsDataTable::class);
    $method = $reflection->getMethod('clientStatusBadge');
    $method->setAccessible(true);
    $dataTable = new ClientsDataTable;

    expect($method->invoke($dataTable, 'active'))
        ->toContain('badge-success')
        ->toContain(__('app.active'))
        ->and($method->invoke($dataTable, 'deactive'))
        ->toContain('badge-secondary')
        ->toContain(__('app.inactive'));
});

it('persists client listing filters in local storage', function () {
    $source = file_get_contents(resource_path('views/clients/index.blade.php'));

    expect($source)
        ->toContain('client-list-filters-v1')
        ->toContain('saveClientListFilters')
        ->toContain('restoreClientListFilters')
        ->toContain('localStorage.setItem')
        ->toContain('localStorage.removeItem');
});

it('provides client listing column presets by role', function () {
    $source = file_get_contents(resource_path('views/clients/index.blade.php'));

    expect($source)
        ->toContain('client-list-column-preset-v1')
        ->toContain('clientListColumnPresets')
        ->toContain('getClientListDataTable')
        ->toContain('data-column-preset="sales"')
        ->toContain('data-column-preset="finance"')
        ->toContain('data-column-preset="logistics"')
        ->toContain('pricing_tier_name')
        ->toContain('contract_pricing_active')
        ->toContain('outstanding_balance');
});

it('keeps an index migration for client contract active lookups', function () {
    $migrationFiles = glob(module_path('Pricing', 'Database/Migrations/*_add_contract_active_lookup_index_to_client_product_pricing_table.php'));

    expect($migrationFiles)->toHaveCount(1);

    $source = file_get_contents($migrationFiles[0]);

    expect($source)
        ->toContain('client_product_pricing_contract_active_lookup_index')
        ->toContain("'company_id', 'client_id', 'is_active', 'start_date', 'end_date'");
});

it('returns pricing tier name on client listing query', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $company = Company::find($actor->company_id);
    $suffix = uniqid('', true);
    $client = ClientImportProcessor::processRow(
        ["Tier Client {$suffix}", "TIER-{$suffix}", "tier.client.{$suffix}@example.test"],
        [0 => 'name', 1 => 'client_code', 2 => 'email'],
        $company
    );

    $tier = PricingTier::create([
        'company_id' => $company->id,
        'name' => 'Gold Tier '.$suffix,
        'priority' => 1,
        'is_active' => true,
    ]);

    $client->clientDetails->pricing_tier_id = $tier->id;
    $client->clientDetails->save();

    $product = Product::factory()->create([
        'company_id' => $company->id,
        'price' => 100,
    ]);

    ClientProductPricing::create([
        'company_id' => $company->id,
        'client_id' => $client->id,
        'product_id' => $product->id,
        'custom_price' => 100,
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'is_active' => true,
    ]);

    $row = (new ClientsDataTable)
        ->query(new User)
        ->where('users.id', $client->id)
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->pricing_tier_name)->toBe($tier->name)
        ->and((bool) $row->contract_pricing_active)->toBeTrue();
});

it('filters client listing query by category', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $company = Company::find($actor->company_id);
    $suffix = uniqid('', true);

    $includedCategory = ClientCategory::create([
        'company_id' => $company->id,
        'category_name' => 'Client List Include '.$suffix,
    ]);

    $excludedCategory = ClientCategory::create([
        'company_id' => $company->id,
        'category_name' => 'Client List Exclude '.$suffix,
    ]);

    $includedClient = ClientImportProcessor::processRow(
        ["Category Include {$suffix}", "CAT-IN-{$suffix}", "cat.in.{$suffix}@example.test"],
        [0 => 'name', 1 => 'client_code', 2 => 'email'],
        $company
    );

    $excludedClient = ClientImportProcessor::processRow(
        ["Category Exclude {$suffix}", "CAT-OUT-{$suffix}", "cat.out.{$suffix}@example.test"],
        [0 => 'name', 1 => 'client_code', 2 => 'email'],
        $company
    );

    $includedClient->clientDetails->category_id = $includedCategory->id;
    $includedClient->clientDetails->save();
    $excludedClient->clientDetails->category_id = $excludedCategory->id;
    $excludedClient->clientDetails->save();

    request()->merge([
        'category_id' => $includedCategory->id,
        'sub_category_id' => 'all',
        'status' => 'all',
        'client' => 'all',
        'project_id' => 'all',
        'contract_type_id' => 'all',
        'country_id' => 'all',
        'verification' => 'all',
        'searchText' => '',
    ]);

    $ids = (new ClientsDataTable)
        ->query(new User)
        ->whereIn('users.id', [$includedClient->id, $excludedClient->id])
        ->pluck('users.id')
        ->all();

    expect($ids)->toContain($includedClient->id)
        ->and($ids)->not->toContain($excludedClient->id);
});

it('updates client custom fields from the edit flow', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    if (user()->permission('edit_clients') == 'none') {
        test()->markTestSkipped('Active admin cannot edit clients.');
    }

    $company = Company::find($actor->company_id);
    $suffix = uniqid('', true);
    $client = ClientImportProcessor::processRow(
        ["Custom Field Client {$suffix}", "CF-{$suffix}", "cf.client.{$suffix}@example.test"],
        [0 => 'name', 1 => 'client_code', 2 => 'email'],
        $company
    );

    $group = CustomFieldGroup::query()->firstOrCreate(
        [
            'name' => 'Client',
            'model' => ClientDetails::CUSTOM_FIELD_MODEL,
            'company_id' => $company->id,
        ]
    );

    $field = CustomField::query()->updateOrCreate(
        [
            'custom_field_group_id' => $group->id,
            'name' => 'uat_client_edit_note',
        ],
        [
            'company_id' => $company->id,
            'label' => 'UAT Client Edit Note',
            'type' => 'text',
            'required' => 'no',
            'export' => 1,
            'visible' => 'false',
        ]
    );

    $response = test()->putJson(route('clients.update', $client->id), [
        'name' => $client->name,
        'email' => $client->email,
        'client_code' => $client->clientDetails->client_code,
        'status' => $client->status,
        'country' => $client->country_id,
        'category_id' => $client->clientDetails->category_id,
        'sub_category_id' => $client->clientDetails->sub_category_id,
        'locale' => $client->locale ?: 'en',
        'redirect_url' => route('clients.index'),
        'custom_fields_data' => [
            'field_'.$field->id => 'Saved from edit flow '.$suffix,
        ],
    ]);

    $response->assertOk();

    expect(DB::table('custom_fields_data')
        ->where('model', ClientDetails::CUSTOM_FIELD_MODEL)
        ->where('model_id', $client->clientDetails->id)
        ->where('custom_field_id', $field->id)
        ->value('value'))->toBe('Saved from edit flow '.$suffix);
});

it('returns outstanding balance on client listing query', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    $company = Company::find($actor->company_id);
    $suffix = uniqid('', true);
    $client = ClientImportProcessor::processRow(
        ["Balance Client {$suffix}", "BAL-{$suffix}", "balance.client.{$suffix}@example.test"],
        [0 => 'name', 1 => 'client_code', 2 => 'email'],
        $company
    );

    $makeInvoice = function (string $status, float $total, bool $creditNote = false) use ($company, $client, $suffix): Invoice {
        $invoice = new Invoice;
        $invoice->company_id = $company->id;
        $invoice->client_id = $client->id;
        $invoice->currency_id = $company->currency_id;
        $invoice->default_currency_id = $company->currency_id;
        $invoice->invoice_number = 'INV-'.$status.'-'.uniqid($suffix);
        $invoice->original_invoice_number = random_int(100000, 999999);
        $invoice->issue_date = now()->subDay();
        $invoice->due_date = now()->addWeek();
        $invoice->sub_total = $total;
        $invoice->total = $total;
        $invoice->due_amount = $total;
        $invoice->status = $status;
        $invoice->credit_note = $creditNote ? 1 : 0;
        $invoice->send_status = 1;
        $invoice->save();

        return $invoice;
    };

    $partialInvoice = $makeInvoice('partial', 120);
    $unpaidInvoice = $makeInvoice('unpaid', 50);
    $makeInvoice('paid', 999);
    $makeInvoice('canceled', 999);
    $makeInvoice('unpaid', 999, true);

    Payment::create([
        'company_id' => $company->id,
        'invoice_id' => $partialInvoice->id,
        'amount' => 20,
        'currency_id' => $company->currency_id,
        'default_currency_id' => $company->currency_id,
        'status' => 'complete',
        'paid_on' => now(),
    ]);

    Payment::create([
        'company_id' => $company->id,
        'invoice_id' => $partialInvoice->id,
        'amount' => 500,
        'currency_id' => $company->currency_id,
        'default_currency_id' => $company->currency_id,
        'status' => 'pending',
        'paid_on' => now(),
    ]);

    $row = (new ClientsDataTable)
        ->query(new User)
        ->where('users.id', $client->id)
        ->first();

    expect($row)->not->toBeNull()
        ->and((float) $row->outstanding_balance)->toBe(150.0)
        ->and($partialInvoice->fresh()->amountDue() + $unpaidInvoice->fresh()->amountDue())->toBe(150.0);
});

it('renders client pricing detail tab', function () {
    $actor = authenticateActiveCompanyAdmin();
    if ($actor === null) {
        test()->markTestSkipped('No active company admin user in database.');
    }

    if (! in_array('pricing', array_map('strtolower', user_modules()))) {
        test()->markTestSkipped('Pricing module is not enabled for the active company.');
    }

    if (user()->permission('view_client_tiers') == 'none' && user()->permission('view_client_pricing') == 'none') {
        test()->markTestSkipped('Active admin cannot view client pricing details.');
    }

    $company = Company::find($actor->company_id);
    $suffix = uniqid('', true);
    $client = ClientImportProcessor::processRow(
        ["Pricing Tab Client {$suffix}", "PTAB-{$suffix}", "pricing.tab.{$suffix}@example.test"],
        [0 => 'name', 1 => 'client_code', 2 => 'email'],
        $company
    );

    $tier = PricingTier::create([
        'company_id' => $company->id,
        'name' => 'Tab Tier '.$suffix,
        'priority' => 1,
        'is_active' => true,
    ]);

    $client->clientDetails->pricing_tier_id = $tier->id;
    $client->clientDetails->save();

    $response = test()->get(route('clients.show', $client->id).'?tab=pricing');

    $response->assertOk()
        ->assertSee('Pricing')
        ->assertSee($tier->name);
});
