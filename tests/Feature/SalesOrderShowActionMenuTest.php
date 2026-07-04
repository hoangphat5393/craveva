<?php

declare(strict_types=1);

it('keeps sales order show action menu icons consistent', function (): void {
    $view = file_get_contents(base_path('resources/views/orders/ajax/show.blade.php'));

    expect($view)->toContain('<i class="fa fa-download mr-2"></i> @lang(\'app.download\')')
        ->and($view)->toContain('<i class="fa fa-check mr-2"></i> @lang(\'app.orderMarkAsComplete\')')
        ->and($view)->toContain('<i class="fa fa-truck-loading mr-2"></i> @lang(\'purchase::app.createDeliveryOrder\')')
        ->and($view)->toContain('<i class="fa fa-industry mr-2"></i> @lang(\'production::app.createProductionOrderFromSalesOrder\')')
        ->and($view)->toContain('<i class="fa fa-trash mr-2"></i> @lang(\'app.delete\')');
});
