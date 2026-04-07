<?php

use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('redirects get account settings developertools to developertools index', function () {
    $userAuth = UserAuth::query()->first();
    $user = $userAuth ? User::query()->where('user_auth_id', $userAuth->id)->first() : null;

    if (! $userAuth || ! $user) {
        test()->markTestSkipped('No user with user_auth in database.');
    }

    $this->actingAs($userAuth);

    $response = $this->get('/account/settings/developertools');

    $response->assertRedirect(route('developertools.index'));
});
