<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'ai-order-webhook/*',
        // Same-domain Postman / ERP clients send cookies; Sanctum stateful stack may
        // otherwise apply CSRF to these API routes. Auth is header secret, not session.
        'api/integrations/*',
        '*-webhook/*',
        '*_webhook/*',
        '*_webhook',
        '*-webhook',
        '/lead-form/leadStore',
        '/lead-form/ticket-store',
        '*/iclock/*',
        '/billing-verify-webhook/*',
        'save-invoices',
        '*/payfast-notification/*',
    ];
}
