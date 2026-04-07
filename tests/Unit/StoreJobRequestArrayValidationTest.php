<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StoreJobRequestArrayValidationTest extends TestCase
{
    public function test_store_job_request_uses_array_rules_for_multi_value_fields(): void
    {
        $path = dirname(__DIR__, 2).'/Modules/Recruit/Http/Requests/StoreJobRequest.php';
        $this->assertFileExists($path);
        $src = file_get_contents($path);
        $this->assertIsString($src);
        $this->assertStringNotContainsString("'location_id.0'", $src);
        $this->assertStringNotContainsString("'skill_id.0'", $src);
        $this->assertStringNotContainsString("'stage_id.0'", $src);
        $this->assertStringContainsString("'location_id' => 'required|array|min:1'", $src);
        $this->assertStringContainsString("'skill_id' => 'required|array|min:1'", $src);
        $this->assertStringContainsString("'stage_id' => 'required|array|min:1'", $src);
    }
}
