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
}
