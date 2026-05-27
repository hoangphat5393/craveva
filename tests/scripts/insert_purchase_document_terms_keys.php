<?php

$base = dirname(__DIR__, 2) . '/Modules/LanguagePack/Languages/modules/Purchase';
$enFile = $base . '/en/modules.php';
$enData = include $enFile;
$enSettings = $enData['purchaseSettings'] ?? [];

$insertBlock = "\n        'documentTermsSection' => " . var_export($enSettings['documentTermsSection'] ?? '', true) . ",\n"
    . "        'documentTermsSectionHelp' => " . var_export($enSettings['documentTermsSectionHelp'] ?? '', true) . ",\n"
    . "        'purchaseOrderTerms' => " . var_export($enSettings['purchaseOrderTerms'] ?? '', true) . ",\n"
    . "        'grnTerms' => " . var_export($enSettings['grnTerms'] ?? '', true) . ",\n"
    . "        'grnTermsHelp' => " . var_export($enSettings['grnTermsHelp'] ?? '', true) . ",";

$viData = include $base . '/vi/modules.php';
$viSettings = $viData['purchaseSettings'] ?? [];
$viBlock = "\n        'documentTermsSection' => " . var_export($viSettings['documentTermsSection'] ?? '', true) . ",\n"
    . "        'documentTermsSectionHelp' => " . var_export($viSettings['documentTermsSectionHelp'] ?? '', true) . ",\n"
    . "        'purchaseOrderTerms' => " . var_export($viSettings['purchaseOrderTerms'] ?? '', true) . ",\n"
    . "        'grnTerms' => " . var_export($viSettings['grnTerms'] ?? '', true) . ",\n"
    . "        'grnTermsHelp' => " . var_export($viSettings['grnTermsHelp'] ?? '', true) . ",";

$patched = 0;

foreach (glob($base . '/*/modules.php') as $file) {
    $locale = basename(dirname($file));
    if (in_array($locale, ['en', 'eng', 'vi'], true)) {
        continue;
    }

    $content = file_get_contents($file);
    if ($content === false || str_contains($content, 'documentTermsSection')) {
        continue;
    }

    $needle = "'termsAndCondition' =>";
    $pos = strpos($content, $needle);
    if ($pos === false) {
        continue;
    }

    $lineEnd = strpos($content, "\n", $pos);
    if ($lineEnd === false) {
        continue;
    }

    $block = $locale === 'vi' ? $viBlock : $insertBlock;
    $content = substr($content, 0, $lineEnd + 1) . $block . substr($content, $lineEnd + 1);

    // Drop legacy delivery-order term keys if present on following lines
    $content = preg_replace(
        "/\n\\s*'deliveryOrderTerms' =>.*?,\n\\s*'deliveryOrderSettingsHelp' =>.*?,\n/s",
        "\n",
        $content
    ) ?? $content;

    file_put_contents($file, $content);
    $patched++;
    echo "Inserted keys: {$locale}\n";
}

echo "Done. {$patched} file(s).\n";
