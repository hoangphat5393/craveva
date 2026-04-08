<?php

use Illuminate\Support\Facades\Route;

it('named billing webhook routes resolve to existing controller methods', function () {
    $names = [
        'billing.save_webhook',
        'billing.verify-webhook',
        'billing.save_razorpay-webhook',
        'billing.save_paystack-webhook',
        'billing.save_paypal-webhook',
        'billing.save_authorize-webhook',
    ];

    foreach ($names as $name) {
        $route = Route::getRoutes()->getByName($name);
        expect($route)->not->toBeNull("Missing route: {$name}");

        $action = $route->getAction('controller') ?? $route->getAction('uses');

        if ($action instanceof Closure) {
            continue;
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$class, $method] = explode('@', $action);
            expect(class_exists($class))->toBeTrue();
            expect(method_exists($class, $method))->toBeTrue("{$class}::{$method} for {$name}");
        } elseif (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            expect(method_exists($class, $method))->toBeTrue("{$class}::{$method} for {$name}");
        }
    }
});

it('registers superadmin billing offline plan download route', function () {
    expect(Route::has('superadmin.billing-offline-plan.download'))->toBeTrue();
});
