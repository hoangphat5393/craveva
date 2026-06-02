<?php

use Illuminate\Support\Facades\Route;
use Modules\Recruit\Http\Controllers\JobController;

it('uses fortify for password reset routes instead of legacy stub controller', function () {
    $route = collect(Route::getRoutes())->first(
        fn($route) => $route->getName() === 'password.request'
    );

    expect($route)->not->toBeNull();

    $action = $route->getAction('uses') ?? $route->getActionName();

    expect($action)->not->toContain('ForgotPasswordController');
    expect(file_exists(app_path('Http/Controllers/ForgotPasswordController.php')))->toBeFalse();
});

it('does not register unused recruit module scaffold controller', function () {
    expect(file_exists(base_path('Modules/Recruit/Http/Controllers/RecruitController.php')))->toBeFalse();

    $recruitRoutes = collect(Route::getRoutes())->filter(
        fn($route) => str_contains($route->getActionName(), 'RecruitController')
    );

    expect($recruitRoutes)->toHaveCount(0);
});

it('still registers active recruit job routes', function () {
    $route = collect(Route::getRoutes())->first(
        fn($route) => str_contains($route->getActionName(), JobController::class)
    );

    expect($route)->not->toBeNull();
});
