<?php

namespace App\Services\Integrations;

use App\Models\Company;
use Illuminate\Http\Request;

final class AiOrderIntegrationAuthService
{
    public const REQUEST_ATTRIBUTE_COMPANY = 'ai_integration_company';

    public function resolveSecretToken(Request $request): string
    {
        $header = (string) $request->header('X-AI-Webhook-Secret', '');
        if ($header !== '') {
            return $header;
        }

        $bearer = (string) ($request->bearerToken() ?? '');

        return $bearer;
    }

    /**
     * Resolve company for REST integration: only rows with non-null per-company secret.
     * Global env secret is not supported for these routes (ambiguous tenant).
     */
    public function resolveCompanyForRestToken(string $token): ?Company
    {
        if ($token === '') {
            return null;
        }

        return Company::withoutGlobalScopes()
            ->whereNotNull('ai_order_webhook_secret')
            ->where('ai_order_webhook_secret', $token)
            ->first();
    }

    public function isGlobalSecretToken(string $token): bool
    {
        $global = (string) config('app.ai_order_webhook_secret', '');

        return $global !== '' && hash_equals($global, $token);
    }
}
