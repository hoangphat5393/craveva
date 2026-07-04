<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

it('registers sales do create route', function (): void {
    expect(Route::has('sales-do.create'))->toBeTrue();
});

it('registers sales do ship form route', function (): void {
    expect(Route::has('sales-do.ship-form'))->toBeTrue()
        ->and(Route::has('sales-shipments.ship-form'))->toBeTrue();
});

it('renders sales do create page for authorized user', function (): void {
    if (! Schema::hasTable('users')) {
        test()->markTestSkipped('Required tables missing.');
    }

    $company = Company::withoutGlobalScopes()->where('status', 'active')->orderBy('id')->first();
    if ($company === null) {
        test()->markTestSkipped('No active company.');
    }

    $user = User::withoutGlobalScopes()
        ->where('company_id', $company->id)
        ->where('status', 'active')
        ->whereNull('is_client_contact')
        ->whereNotNull('user_auth_id')
        ->orderBy('id')
        ->first();

    if ($user === null) {
        test()->markTestSkipped('No active employee user.');
    }

    $userAuth = UserAuth::find($user->user_auth_id);
    if ($userAuth === null) {
        test()->markTestSkipped('User auth missing.');
    }

    $response = $this->actingAs($userAuth, 'web')
        ->get(route('sales-do.create'));

    if ($response->status() === 403) {
        test()->markTestSkipped('Current user lacks sales_do.create permission.');
    }

    $response->assertSuccessful();
    $response->assertSee(__('purchase::app.menu.saleDeliveryOrder'), false);
});

it('uses warehouse batch id as the sales do batch source of truth', function (): void {
    $itemsView = file_get_contents(base_path('Modules/Purchase/Resources/views/sales-shipment/ajax/items.blade.php'));
    $service = file_get_contents(base_path('Modules/Purchase/Services/SalesDoService.php'));
    $controller = file_get_contents(base_path('Modules/Purchase/Http/Controllers/SalesShipmentController.php'));

    expect($itemsView)->toContain('name="warehouse_batch_id[]"')
        ->and($itemsView)->toContain('data-container="body"')
        ->and($itemsView)->toContain('data-size="8"')
        ->and($itemsView)->toContain("find('select.shipment-batch-select')")
        ->and($itemsView)->toContain("$('select.shipment-batch-select').selectpicker()")
        ->and($itemsView)->not->toContain('name="warehouse_batch_ui[]"')
        ->and($itemsView)->not->toContain('shipment-batch-id-input')
        ->and($service)->toContain('! empty($warehouseBatchId) ? (int) $warehouseBatchId : null')
        ->and($controller)->toContain('$validated[\'batch_number\'][$idx] = $batch->batch_number')
        ->and($controller)->toContain('$validated[\'expiration_date\'][$idx] = $batch->expiration_date');
});

it('keeps sales do note and terms in a separate two-column row', function (): void {
    $createView = file_get_contents(base_path('Modules/Purchase/Resources/views/sales-shipment/ajax/create.blade.php'));
    $editView = file_get_contents(base_path('Modules/Purchase/Resources/views/sales-shipment/ajax/edit.blade.php'));

    foreach ([$createView, $editView] as $view) {
        expect($view)->toContain('class="row p-20"')
            ->and($view)->toContain('class="col-md-6 col-sm-12"')
            ->and($view)->toContain("'wrapperClass' => 'col-md-6 col-sm-12 c-inv-note-terms'")
            ->and($view)->not->toContain('d-flex flex-wrap');
    }
});

it('opens a dedicated ship form from sales do overview', function (): void {
    $overviewView = file_get_contents(base_path('Modules/Purchase/Resources/views/sales-shipment/ajax/overview.blade.php'));
    $shipView = file_get_contents(base_path('Modules/Purchase/Resources/views/sales-shipment/ajax/ship.blade.php'));
    $controller = file_get_contents(base_path('Modules/Purchase/Http/Controllers/SalesShipmentController.php'));

    expect($overviewView)->toContain('route($salesDoRoutePrefix . \'.ship-form\', $shipment->id)')
        ->and($overviewView)->toContain('class="dropdown-item f-14 text-dark openRightModal"')
        ->and($shipView)->toContain('id="ship-sales-shipment-form"')
        ->and($shipView)->toContain("@include('purchase::sales-shipment.ajax.items')")
        ->and($shipView)->toContain('route($salesDoRoutePrefix . \'.ship\', $shipment->id)')
        ->and($controller)->toContain('public function shipForm($id)')
        ->and($controller)->toContain('$request->has(\'order_item_id\')')
        ->and($controller)->toContain('route($this->salesDoRouteName(\'ship-form\'), $shipment->id)');
});
