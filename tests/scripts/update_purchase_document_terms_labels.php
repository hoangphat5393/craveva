<?php

declare(strict_types=1);

$replacements = [
    "'documentTermsSection' => 'Document terms (PO & GRN)'" => "'documentTermsSection' => 'Document terms (Purchase order & Goods receipt note)'",
    "'documentTermsSectionHelp' => 'Company-wide terms shown on purchase orders and goods receipt notes (GRN). Per-document notes are entered on each form.'" => "'documentTermsSectionHelp' => 'Company-wide terms shown on purchase orders and goods receipt notes. Per-document notes are entered on each form.'",
    "'purchaseOrderTerms' => 'Purchase order (PO) — Terms and Conditions'" => "'purchaseOrderTerms' => 'Purchase order & Goods receipt note — Terms and Conditions',\n        'purchaseOrderAndGrnTerms' => 'Purchase order & Goods receipt note — Terms and Conditions'",
    "'grnTerms' => 'GRN — Terms and Conditions'" => "'grnTerms' => 'Purchase order & Goods receipt note — Terms and Conditions'",
    "'grnTermsHelp' => 'If empty, purchase order terms are used on GRN forms and PDFs.'" => "'grnTermsHelp' => 'Shared with purchase order terms on all purchase documents.'",
];

$paths = array_merge(
    glob(__DIR__ . '/../../Modules/LanguagePack/Languages/modules/Purchase/*/modules.php') ?: [],
    glob(__DIR__ . '/../../Modules/Purchase/Resources/lang/*/modules.php') ?: [],
);

foreach ($paths as $file) {
    if (preg_match('#/(en|vi|eng)/#', $file)) {
        continue;
    }

    $content = file_get_contents($file);
    if ($content === false || ! str_contains($content, 'documentTermsSection')) {
        continue;
    }

    foreach ($replacements as $from => $to) {
        $content = str_replace($from, $to, $content);
    }

    file_put_contents($file, $content);
}

echo "Updated purchase document terms labels in locale files.\n";
