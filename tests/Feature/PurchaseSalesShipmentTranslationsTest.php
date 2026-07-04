<?php

it('translates purchase sales shipment status keys in English', function () {
    app()->setLocale('en');

    expect(__('purchase::modules.salesShipment.draft'))->toBe('Draft');
    expect(__('purchase::app.createDeliveryOrder'))->toBe('Create Delivery Order');
    expect(__('purchase::app.shipDeliveryOrder'))->toBe('Ship Delivery Order');
    expect(__('messages.salesDoShipQuantityRequired'))->toContain('Open the Ship form');
    expect(__('purchase::modules.salesShipment.confirmed'))->toBe('Confirmed');
    expect(__('purchase::modules.salesShipment.shipped'))->toBe('Shipped (Stock Out)');
    expect(__('purchase::modules.salesShipment.delivered'))->toBe('Delivered');
    expect(__('purchase::modules.salesShipment.markDelivered'))->toBe('Mark Delivered');
    expect(__('purchase::modules.salesShipment.reverse'))->toBe('Reverse Shipment');
    expect(__('purchase::modules.salesShipment.cancelled'))->toBe('Cancelled');
    expect(__('modules.invoices.downloadPdf'))->not->toBe('modules.invoices.downloadPdf');
});

it('translates purchase sales shipment status keys in Vietnamese', function () {
    app()->setLocale('vi');

    expect(__('purchase::modules.salesShipment.draft'))->toBe('Nháp');
    expect(__('purchase::app.createDeliveryOrder'))->toBe('Tạo phiếu giao hàng');
    expect(__('purchase::app.shipDeliveryOrder'))->toBe('Xuất kho phiếu giao hàng');
    expect(__('messages.salesDoShipQuantityRequired'))->toContain('mở form Ship');
    expect(__('purchase::modules.salesShipment.shipped'))->toBe('Đã xuất kho');
    expect(__('purchase::modules.salesShipment.markDelivered'))->toBe('Đánh dấu đã giao khách');
    expect(__('purchase::modules.salesShipment.reverse'))->toBe('Hoàn tác xuất kho');
    expect(__('purchase::modules.salesShipment.cancelled'))->toBe('Đã hủy');
});
