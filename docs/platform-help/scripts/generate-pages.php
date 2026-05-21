<?php

/**
 * Generate page markdown from 00-URL-INDEX.md (English, in-corpus links only).
 * Usage: php docs/platform-help/scripts/generate-pages.php [--force]
 */

$base = dirname(__DIR__);
$indexPath = $base . '/00-URL-INDEX.md';
$force = in_array('--force', $argv ?? [], true);

if (! is_file($indexPath)) {
    fwrite(STDERR, "Missing 00-URL-INDEX.md\n");
    exit(1);
}

$lines = file($indexPath, FILE_IGNORE_NEW_LINES);
$created = 0;
$skipped = 0;

foreach ($lines as $line) {
    if (! str_starts_with($line, '| `/account')) {
        continue;
    }

    if (! preg_match(
        '#\| `(/account[^`]+)` \| `([^`]+)` \| ([^|]+) \| \[([^\]]+)\]\((pages/[^)]+)\)#',
        $line,
        $m
    )) {
        continue;
    }

    [, $url, $routeName, $module,, $relPath] = $m;
    $module = trim($module);
    $fullPath = $base . '/' . $relPath;

    if (is_file($fullPath) && ! $force) {
        $skipped++;

        continue;
    }

    $dir = dirname($fullPath);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $stem = explode('.', $routeName)[0];
    $title = humanTitle($stem);
    $rich = richContent($stem);

    $content = buildPage($title, $url, $routeName, $module, $stem, $rich);
    file_put_contents($fullPath, $content);
    $created++;
}

echo "Created/updated: {$created}, skipped: {$skipped}\n";

function humanTitle(string $stem): string
{
    return ucwords(str_replace(['-', '_'], ' ', $stem));
}

/**
 * @return array<string, mixed>
 */
function richContent(string $stem): array
{
    $map = [
        'purchase-products' => [
            'purpose' => 'Manage products (SKU, pricing, alternate UOM, inventory flags).',
            'menu' => 'Operations → Products',
            'permissions' => 'view_product, add_product, edit_product',
            'steps' => "1. Open **Products**.\n2. **Add Product** (right modal) or **Edit** on a row.\n3. Set **Classification** (Unit Type) and **Pricing** (Selling Price > 0) before **+ Add alternate UOM**.\n4. **Save**.",
            'fields' => "| Unit Type | Base unit (Classification) |\n| Selling Price | Required before alternate UOM rows |\n| + Add alternate UOM | Extra selling units (case, pack, etc.) |",
            'errors' => "| Add UOM disabled | Missing unit or zero price | Set Classification + Pricing |\n| UOM dropdown clipped | table-responsive overflow | Use bootstrap-select `container: body` |",
            'related' => '[30-product-and-uom.md](../flows/30-product-and-uom.md), [BUSINESS-FLOWS-SUMMARY.md](../REFERENCE/BUSINESS-FLOWS-SUMMARY.md)',
        ],
        'orders' => [
            'purpose' => 'Sales orders — lines, UOM, optional tier pricing.',
            'menu' => 'Operations → Sale Orders (or Sales → Orders)',
            'permissions' => 'view_order, add_order, edit_order',
            'steps' => "1. **Add Order**.\n2. Select **Client**, add product lines.\n3. Choose **UOM** per line when applicable.\n4. Save → create DO / Invoice (see flow 20).",
            'related' => '[20-so-do-invoice-warehouse.md](../flows/20-so-do-invoice-warehouse.md)',
        ],
        'dashboard' => [
            'purpose' => 'Post-login overview — KPI widgets and shortcuts.',
            'menu' => 'Home → Private Dashboard / Advanced Dashboard',
            'permissions' => 'view_overview_dashboard (per widget)',
            'steps' => "1. After login, land on `/account/dashboard`.\n2. Use widgets; click tiles to open modules.",
            'related' => '[ERP-SYSTEM-OVERVIEW.md](../REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        ],
        'purchase-order' => [
            'purpose' => 'Purchase orders to vendors.',
            'menu' => 'Operations → Purchase Order',
            'permissions' => 'view_purchase_order, add_purchase_order',
            'steps' => "1. **Add** PO.\n2. Select vendor and lines with UOM.\n3. Save → GRN / Bill (flow 10).",
            'related' => '[10-po-to-grn-vendor-pay.md](../flows/10-po-to-grn-vendor-pay.md)',
        ],
        'warehouse' => [
            'purpose' => 'Warehouses and warehouse-level settings.',
            'menu' => 'Operations → Warehouses',
            'permissions' => 'view_warehouse',
            'related' => '[BUSINESS-FLOWS-SUMMARY.md](../REFERENCE/BUSINESS-FLOWS-SUMMARY.md), [20-so-do-invoice-warehouse.md](../flows/20-so-do-invoice-warehouse.md)',
        ],
        'clients' => [
            'purpose' => 'Customer accounts — orders, invoices, projects.',
            'menu' => 'Sales → Clients',
            'permissions' => 'view_client, add_client',
            'related' => '[ERP-SYSTEM-OVERVIEW.md](../REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        ],
        'projects' => [
            'purpose' => 'Projects — tasks, timesheet, members.',
            'menu' => 'Work Management → Projects',
            'permissions' => 'view_projects',
        ],
        'estimates' => [
            'purpose' => 'Quotes/estimates — convertible to order or invoice.',
            'menu' => 'Sales → Quotation / Estimates',
            'permissions' => 'view_estimates, add_estimates',
            'related' => '[20-so-do-invoice-warehouse.md](../flows/20-so-do-invoice-warehouse.md)',
        ],
        'invoices' => [
            'purpose' => 'Customer invoices — payments and credit notes.',
            'menu' => 'Finance → Invoices',
            'permissions' => 'view_invoices, add_invoices',
            'related' => '[20-so-do-invoice-warehouse.md](../flows/20-so-do-invoice-warehouse.md)',
        ],
        'payments' => [
            'purpose' => 'Incoming payments — allocate to invoices.',
            'menu' => 'Finance → Payments',
            'permissions' => 'view_payments',
        ],
    ];

    return $map[$stem] ?? [];
}

/**
 * @param  array<string, mixed>  $rich
 */
function buildPage(string $title, string $url, string $routeName, string $module, string $stem, array $rich): string
{
    $purpose = $rich['purpose'] ?? "Manage **{$title}** in the current company.";
    $menu = $rich['menu'] ?? "Sidebar group for module `{$module}` (module must be enabled).";
    $permissions = $rich['permissions'] ?? 'see Settings → Roles (permission keys vary by resource)';
    $steps = $rich['steps'] ?? "1. Open list: `{$url}`.\n2. **Add** / **Edit** (usually right modal).\n3. Fill form → **Save**.\n4. Use list filters, export, quick actions if shown.";
    $fields = $rich['fields'] ?? '| (on-screen labels) | See form on screen |';
    $errors = $rich['errors'] ?? '| 403 | Module/permission | Enable module; grant role permissions |';
    $related = $rich['related'] ?? '[01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md)';

    $crud = "- List: `{$routeName}` → `{$url}`\n- Create/Edit/Show: `{$stem}.create`, `.edit`, `.show` (often AJAX modal; list URL unchanged)";

    return <<<MD
# {$title}

URL: {$url}
Route name: {$routeName}
Roles: admin (typical); employee/client per permission
Permissions: {$permissions}
Modules: {$module}
Related routes:

{$crud}

## Purpose

{$purpose}

## Who uses it / access

- Requires module **`{$module}`** in `user_modules()` and matching permission.
- Role details: [01-ROLES-AND-ACCESS.md](../01-ROLES-AND-ACCESS.md).

## How to open the screen

{$menu}

## Steps

{$steps}

## Important fields and buttons

{$fields}

## Expected results

- After save: return to list or close modal; row appears/updates in DataTable.
- AJAX forms: success toast; validation errors show red borders + toast ([UI-CONVENTIONS.md](../REFERENCE/UI-CONVENTIONS.md)).

## Common errors

{$errors}

## FAQ

**Q:** Menu item missing?
**A:** Check **Module Settings** and subscription package.

## Related

{$related}

MD;
}
