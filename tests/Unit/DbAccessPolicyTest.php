<?php

namespace Tests\Unit;

use Modules\DeveloperTools\Services\DbAccessPolicy;
use Tests\TestCase;

class DbAccessPolicyTest extends TestCase
{
    public function test_it_adds_module_dependencies(): void
    {
        $policy = new DbAccessPolicy;

        $modules = $policy->normalizeRequestedModules(['pricing']);

        $this->assertContains('pricing', $modules);
        $this->assertContains('core', $modules);
    }

    public function test_it_uses_defaults_when_empty(): void
    {
        $policy = new DbAccessPolicy;

        $modules = $policy->normalizeRequestedModules([]);

        $this->assertNotEmpty($modules);
    }

    public function test_it_matches_percent_wildcards(): void
    {
        $policy = new DbAccessPolicy;

        $schemaTables = ['products', 'pricing_tiers', 'pricing_tier_items', 'warehouse_product_stock'];
        $matched = $policy->matchTablesByPatterns($schemaTables, ['pricing_%']);

        $this->assertContains('pricing_tiers', $matched);
        $this->assertContains('pricing_tier_items', $matched);
        $this->assertNotContains('products', $matched);
    }

    public function test_it_sanitizes_identifiers(): void
    {
        $policy = new DbAccessPolicy;

        $this->assertSame('api_gateway_20', $policy->sanitizeIdentifier('api_gateway_20'));
        $this->assertSame('api_gateway_20', $policy->sanitizeIdentifier('api-gateway-20'));
        $this->assertSame('', $policy->sanitizeIdentifier('!!!'));
    }
}
