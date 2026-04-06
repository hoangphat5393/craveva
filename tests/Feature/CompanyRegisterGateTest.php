<?php

use App\Models\GlobalSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('rejects public company signup when enable_register is off', function () {
    if (! isCraveva()) {
        test()->markTestSkipped('SuperAdmin public signup routes require Craveva.');
    }

    $global = GlobalSetting::first();
    if (! $global) {
        test()->markTestSkipped('No global settings row.');
    }

    $global->registration_open = 1;
    $global->enable_register = 0;
    $global->saveQuietly();
    cache()->forget('global_setting');

    $email = 'gate_test_' . uniqid('', true) . '@example.com';

    $response = $this->from(route('front.signup.index'))
        ->post(route('front.signup.store'), [
            'company_name' => 'Gate Test Co',
            'name' => 'Gate User',
            'email' => $email,
            'password' => 'passwordpassword',
            'password_confirmation' => 'passwordpassword',
        ]);

    $response->assertForbidden();
});

it('validates all required signup fields when subdomain format is invalid', function () {
    if (! isCraveva()) {
        test()->markTestSkipped('SuperAdmin public signup routes require Craveva.');
    }

    if (! module_enabled('Subdomain')) {
        test()->markTestSkipped('Subdomain module is disabled.');
    }

    $global = GlobalSetting::first();
    if (! $global) {
        test()->markTestSkipped('No global settings row.');
    }

    $global->registration_open = 1;
    $global->enable_register = 1;
    $global->sign_up_terms = 'no';
    $global->google_recaptcha_status = 'deactive';
    $global->google_recaptcha_v2_status = 'deactive';
    $global->saveQuietly();
    cache()->forget('global_setting');

    $domain = getDomain();
    $badSub = '9bad.' . $domain;

    $response = $this->from(route('front.signup.index'))
        ->post(route('front.signup.store'), [
            'company_name' => '',
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'sub_domain' => $badSub,
        ]);

    $response->assertSessionHasErrors(['company_name', 'name', 'email', 'password', 'sub_domain']);
});
