<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

it('rejects warehouse flow payload when both inbound sources are enabled', function () {
    $data = [
        'allow_negative_stock' => false,
        'strict_unit_conversion' => false,
        'inbound_from_purchase_order_delivered' => true,
        'inbound_from_delivery_order_received' => true,
        'sales_outbound_enabled' => true,
        'sales_outbound_mode' => 'shipment',
        'ai_order_webhook_check_stock' => true,
    ];

    $validator = Validator::make($data, [
        'allow_negative_stock' => ['required', 'boolean'],
        'strict_unit_conversion' => ['required', 'boolean'],
        'inbound_from_purchase_order_delivered' => ['required', 'boolean'],
        'inbound_from_delivery_order_received' => ['required', 'boolean'],
        'sales_outbound_enabled' => ['required', 'boolean'],
        'sales_outbound_mode' => ['required', Rule::in(['shipment', 'invoice'])],
        'ai_order_webhook_check_stock' => ['required', 'boolean'],
    ]);

    $validator->after(function ($validator) use ($data): void {
        if (
            filter_var($data['inbound_from_purchase_order_delivered'], FILTER_VALIDATE_BOOLEAN)
            && filter_var($data['inbound_from_delivery_order_received'], FILTER_VALIDATE_BOOLEAN)
        ) {
            $validator->errors()->add(
                'inbound_from_delivery_order_received',
                __('warehouse::app.err_inbound_both_sources_true')
            );
        }
    });

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->has('inbound_from_delivery_order_received'))->toBeTrue();
});
