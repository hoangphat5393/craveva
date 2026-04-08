<?php

use App\DataTables\ClientsDataTable;
use App\Imports\ClientImport;
use App\Jobs\ImportClientChunkJob;
use App\Models\Company;
use App\Models\User;
use App\Models\UserAuth;
use App\Services\ClientImportProcessor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;

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
