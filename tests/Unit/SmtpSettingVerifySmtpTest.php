<?php

namespace Tests\Unit;

use App\Models\SmtpSetting;
use Tests\TestCase;

class SmtpSettingVerifySmtpTest extends TestCase
{
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
