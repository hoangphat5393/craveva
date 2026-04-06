<?php

namespace Tests\Feature;

use App\Helper\Files;
use App\Models\StorageSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyLogoUploadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_upload_local_or_s3_creates_directory_for_local_disk()
    {
        StorageSetting::query()->update(['status' => 'disabled']);

        StorageSetting::create([
            'filesystem' => 'local',
            'auth_keys' => null,
            'status' => 'enabled',
        ]);

        config(['filesystems.default' => 'local']);

        $dir = 'test-upload/'.uniqid('company-logo-', true);
        $file = UploadedFile::fake()->image('logo.png', 120, 120);

        $name = Files::uploadLocalOrS3($file, $dir);

        $this->assertNotEmpty($name);
        $this->assertTrue(Storage::disk('local')->exists($dir.'/'.$name));
    }
}
