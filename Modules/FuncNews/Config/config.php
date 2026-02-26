<?php

return [
    'scan_paths' => [
        base_path('Modules/DeveloperTools'),
        base_path('resources/views/components/setting-sidebar.blade.php'),
        base_path('routes/web-settings.php'),
        base_path('Modules/DeveloperTools/Providers'),
        base_path('Modules/DeveloperTools/Routes'),
    ],
    'allowed_extensions' => [
        'php', 'blade.php', 'json', 'md', 'css', 'js'
    ],
];
