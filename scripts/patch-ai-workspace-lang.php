<?php

/**
 * One-off: add AI Workspace keys to LanguagePack modules.php for locales missing them.
 */

$base = dirname(__DIR__) . '/Modules/LanguagePack/Languages/app';

$keys = <<<'KEYS'
        'aiWorkspaceHelp' => 'Configure the AI chat widget for all company admins and super admins. Leave both Agent ID and API base empty to hide the AI Workspace menu.',
        'aiWorkspaceAgentId' => 'AI agent ID',
        'aiWorkspaceApiBase' => 'AI API base URL',
        'aiWorkspaceApiBasePlaceholder' => 'https://ai.example.com',
        'aiWorkspaceApiKey' => 'AI API key (optional)',
        'aiWorkspaceApiKeyPlaceholder' => 'Leave blank to keep the current key',
        'aiWorkspaceApiKeyHelp' => 'Optional. Only enter a new key when you want to replace the stored key.',
        'aiWorkspaceRemoveApiKey' => 'Remove stored API key',
KEYS;

$dirs = glob($base . '/*/modules.php') ?: [];

foreach ($dirs as $file) {
    if (preg_match('#/app/(en|vi)/#', $file)) {
        continue;
    }
    $c = file_get_contents($file);
    if ($c === false || str_contains($c, 'aiWorkspaceHelp')) {
        continue;
    }
    $pattern = "/('chooseGoogleRecaptcha'\\s*=>\\s*'[^']*',)\\s*\\n(\\s*)\\],\\s*\\n(\\s*)'profile'\\s*=>/s";
    if (! preg_match($pattern, $c)) {
        fwrite(STDERR, "SKIP (pattern): {$file}\n");

        continue;
    }
    $replacement = '$1' . "\n" . $keys . "\n" . '$2' . '],' . "\n" . '$3' . "'profile' =>";
    $new = preg_replace($pattern, $replacement, $c, 1);
    if ($new === null || $new === $c) {
        fwrite(STDERR, "FAIL: {$file}\n");

        continue;
    }
    file_put_contents($file, $new);
    echo "OK: {$file}\n";
}
