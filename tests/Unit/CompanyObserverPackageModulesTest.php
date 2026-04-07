<?php

namespace Tests\Unit;

use App\Observers\CompanyObserver;
use PHPUnit\Framework\TestCase;

class CompanyObserverPackageModulesTest extends TestCase
{
    public function test_package_module_names_normalizes_json_object_to_lowercase_values(): void
    {
        $json = '{"53":"cybersecurity","54":"developertools","55":"einvoice"}';

        $names = CompanyObserver::packageModuleNamesFromJson($json);

        $this->assertSame(['cybersecurity', 'developertools', 'einvoice'], $names);
    }

    public function test_package_module_names_normalizes_list_json(): void
    {
        $json = '["Developertools","clients"]';

        $names = CompanyObserver::packageModuleNamesFromJson($json);

        $this->assertSame(['developertools', 'clients'], $names);
    }

    public function test_package_module_names_appends_warehouse_when_purchase_present(): void
    {
        $json = '["purchase","clients"]';

        $names = CompanyObserver::packageModuleNamesFromJson($json);

        $this->assertContains('warehouse', $names);
        $this->assertContains('purchase', $names);
    }

    public function test_package_module_names_appends_warehouse_when_products_present(): void
    {
        $json = '["products"]';

        $names = CompanyObserver::packageModuleNamesFromJson($json);

        $this->assertContains('warehouse', $names);
    }

    public function test_package_module_names_does_not_duplicate_warehouse(): void
    {
        $json = '["purchase","warehouse"]';

        $names = CompanyObserver::packageModuleNamesFromJson($json);

        $this->assertSame(1, count(array_filter($names, static fn($n) => $n === 'warehouse')));
    }
}
