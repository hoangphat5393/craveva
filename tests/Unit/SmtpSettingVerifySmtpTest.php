<?php

namespace Tests\Unit;

use App\Models\SmtpSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SmtpSettingVerifySmtpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('smtp_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('mail_driver');
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('smtp_settings');

        parent::tearDown();
    }

    public function test_verify_smtp_does_not_throw_when_mail_password_is_null(): void
    {
        $setting = new SmtpSetting;
        $setting->mail_driver = 'smtp';
        $setting->mail_host = '127.0.0.1';
        $setting->mail_port = '1';
        $setting->mail_encryption = 'tls';
        $setting->mail_username = 'user';
        $setting->mail_password = null;
        $setting->verified = 0;

        $result = $setting->verifySmtp();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
    }
}
