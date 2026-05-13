<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\Integrations\AiOrderIntegrationAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiOrderIntegrationMethodAllowed
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->attributes->get(AiOrderIntegrationAuthService::REQUEST_ATTRIBUTE_COMPANY);

        if (! $company instanceof Company) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized integration request.',
            ], 401);
        }

        $allowed = $this->isMethodAllowed($company, $request->method());

        if (! $allowed) {
            return response()->json([
                'status' => 'error',
                'code' => 'INTEGRATION_METHOD_DISABLED',
                'message' => 'This HTTP method is disabled for AI order integration in company settings.',
            ], 403);
        }

        return $next($request);
    }

    private function isMethodAllowed(Company $company, string $method): bool
    {
        return match (strtoupper($method)) {
            'POST' => (bool) $company->ai_order_integration_allow_create,
            'GET' => (bool) $company->ai_order_integration_allow_read,
            'PUT', 'PATCH' => (bool) $company->ai_order_integration_allow_update,
            'DELETE' => (bool) $company->ai_order_integration_allow_delete,
            default => false,
        };
    }
}
