<?php

declare(strict_types=1);

it('exposes craveva ai sidebar labels in english', function (): void {
    app()->setLocale('en');

    expect(__('app.menu.cravevaAi'))->toBe('Craveva AI')
        ->and(__('app.menu.aiWorkspaceSubmenu'))->toBe('Workspace')
        ->and(__('app.menu.aiAssistantSubmenu'))->toBe('Assistant');
});

it('exposes craveva ai sidebar labels in vietnamese', function (): void {
    app()->setLocale('vi');

    expect(__('app.menu.cravevaAi'))->toBe('Craveva AI')
        ->and(__('app.menu.aiWorkspaceSubmenu'))->toBe('Không gian làm việc')
        ->and(__('app.menu.aiAssistantSubmenu'))->toBe('Trợ lý');
});

it('ai sidebar partial uses single craveva ai accordion', function (): void {
    $path = resource_path('views/sections/partials/ai-sidebar-menu-items.blade.php');

    $contents = file_get_contents($path);

    expect($contents)->toContain("__('app.menu.cravevaAi')")
        ->and($contents)->toContain('js-ai-assistant-widget-toggle')
        ->and($contents)->toContain('target="_blank"')
        ->and($contents)->toContain('route(\'ai-workspace.index\')')
        ->and($contents)->not->toContain('ai-assistant-widget-menu-item');
});
