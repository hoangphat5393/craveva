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

    public function test_inventory_module_includes_core_and_warehouse_dependencies(): void
    {
        $policy = new DbAccessPolicy;

        $modules = $policy->normalizeRequestedModules(['inventory']);

        $this->assertContains('inventory', $modules);
        $this->assertContains('core', $modules);
        $this->assertContains('warehouse', $modules);
    }

    public function test_available_modules_includes_inventory(): void
    {
        $policy = new DbAccessPolicy;

        $available = $policy->availableModules();

        $this->assertArrayHasKey('inventory', $available);
        $this->assertArrayHasKey('label', $available['inventory']);
    }

    public function test_available_modules_for_ui_excludes_internal_only_modules(): void
    {
        $policy = new DbAccessPolicy;

        $forUi = $policy->availableModulesForUi();

        $this->assertArrayNotHasKey('custom_fields', $forUi);
        $this->assertArrayHasKey('inventory', $forUi);
    }

    public function test_custom_fields_module_and_join_view_are_configured(): void
    {
        $policy = new DbAccessPolicy;

        $modules = $policy->availableModules();
        $this->assertArrayHasKey('custom_fields', $modules);
        $this->assertTrue($modules['custom_fields']['internal_only'] ?? false);

        $joinViews = $policy->joinViews();
        $this->assertArrayHasKey('custom_fields_data', $joinViews);
        $this->assertArrayHasKey('from', $joinViews['custom_fields_data']);
        $this->assertArrayHasKey('where', $joinViews['custom_fields_data']);

        $implicit = config('developertools.db_access.implicit_modules_on_credential', []);
        $this->assertContains('custom_fields', $implicit);
    }

    public function test_recruit_module_and_join_views_are_configured(): void
    {
        $policy = new DbAccessPolicy;

        $modules = $policy->availableModules();
        $this->assertArrayHasKey('recruit', $modules);
        $this->assertContains('core', $modules['recruit']['depends_on'] ?? []);

        $sensitive = $policy->sensitiveTables();
        $this->assertArrayHasKey('recruit_global_settings', $sensitive);
        $this->assertTrue($sensitive['recruit_global_settings']['deny'] ?? false);

        $joinViews = $policy->joinViews();
        foreach (['recruit_job_questions', 'job_interview_stages', 'offer_letter_histories', 'recruit_jobboard_settings'] as $table) {
            $this->assertArrayHasKey($table, $joinViews, "Missing join_views for {$table}");
        }
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
