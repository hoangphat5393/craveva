<?php

return [
    'name' => 'DeveloperTools',
    'scan_paths' => [
        base_path('Modules/DeveloperTools'),
        base_path('app'),
        base_path('database'),
        base_path('routes'),
    ],
    'allowed_extensions' => [
        'php', 'blade.php', 'json', 'md', 'css', 'js'
    ],
];
