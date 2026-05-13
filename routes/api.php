<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\Integrations\AiIntegrationOrdersController;
use Froiden\RestAPI\Facades\ApiRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
| Lightweight probe (no auth): confirms this app’s /api/integrations prefix is
| reachable. If Postman gets 404 here, the hostname is not serving this codebase.
*/

Route::get('integrations/__route_probe', static function (): JsonResponse {
    return response()->json([
        'ok' => true,
        'message' => 'This Laravel app registers /api/integrations/*. Use POST /api/integrations/orders with X-AI-Webhook-Secret (or Authorization: Bearer) to create an order.',
    ]);
})->name('api.integrations.route_probe');

/*
| AI order REST paths use Illuminate Route:: (not ApiRoute::) so they stay at
| /api/integrations/... without Froiden's default /api/v1/... version prefix.
| Register them before ApiRoute::group so they win dispatch order on installs
| where the RestAPI package scans /api first.
*/
Route::middleware(['ai.integration.auth', 'ai.integration.method'])
    ->prefix('integrations')
    ->group(function (): void {
        Route::post('orders', [AiIntegrationOrdersController::class, 'store'])->name('api.integrations.orders.store');
        Route::get('orders/{orderId}', [AiIntegrationOrdersController::class, 'show'])->whereNumber('orderId')->name('api.integrations.orders.show');
        Route::patch('orders/{orderId}', [AiIntegrationOrdersController::class, 'update'])->whereNumber('orderId')->name('api.integrations.orders.update');
        Route::put('orders/{orderId}', [AiIntegrationOrdersController::class, 'update'])->whereNumber('orderId')->name('api.integrations.orders.update.put');
        Route::delete('orders/{orderId}', [AiIntegrationOrdersController::class, 'destroy'])->whereNumber('orderId')->name('api.integrations.orders.destroy');
    });

ApiRoute::group(['namespace' => 'App\Http\Controllers'], function () {
    ApiRoute::get('purchased-module', ['as' => 'api.purchasedModule', 'uses' => 'HomeController@installedModule']);
});
