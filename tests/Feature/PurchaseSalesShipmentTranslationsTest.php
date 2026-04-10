<?php

it('translates purchase sales shipment status keys in English', function () {
    app()->setLocale('en');

    expect(__('purchase::modules.salesShipment.draft'))->toBe('Draft');
    expect(__('purchase::modules.salesShipment.confirmed'))->toBe('Confirmed');
    expect(__('purchase::modules.salesShipment.shipped'))->toBe('Shipped');
    expect(__('purchase::modules.salesShipment.delivered'))->toBe('Delivered');
    expect(__('purchase::modules.salesShipment.cancelled'))->toBe('Cancelled');
});

it('translates purchase sales shipment status keys in Vietnamese', function () {
    app()->setLocale('vi');

    expect(__('purchase::modules.salesShipment.draft'))->toBe('Nháp');
    expect(__('purchase::modules.salesShipment.cancelled'))->toBe('Đã hủy');
});
