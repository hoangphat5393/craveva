<?php

namespace App\Http\Middleware;

use App\Services\Integrations\AiOrderIntegrationAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAiOrderIntegration
{
    public function __construct(
        private readonly AiOrderIntegrationAuthService $authService
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->authService->resolveSecretToken($request);

        if ($this->authService->isGlobalSecretToken($token)) {
            return response()->json([
                'status' => 'error',
                'code' => 'INTEGRATION_REST_REQUIRES_COMPANY_SECRET',
                'message' => 'Per-company webhook secret is required for REST integration routes. Configure companies.ai_order_webhook_secret and send it via X-AI-Webhook-Secret or Authorization: Bearer.',
            ], 401);
        }

        $company = $this->authService->resolveCompanyForRestToken($token);

        if ($company === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized integration request.',
            ], 401);
        }

        $request->attributes->set(AiOrderIntegrationAuthService::REQUEST_ATTRIBUTE_COMPANY, $company);

        return $next($request);
    }
}
