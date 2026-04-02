# PowerShell Script: Upload to craveva-hub-server using Zip (Upload to Home)
# Optional: -HubEnsureGit installs git via apt on the hub; Step 0 verifies Git and optional pull from GitHub (-HubGitPull).
# GitHub: https://github.com/CRAVEVA/craveva-hub-server (HTTPS remote in -HubGitRemote).

param(
    [switch]$HubGitPull,
    # Bắt buộc local main khớp origin/main (orphan / zip + git init / "No commits yet") — dùng git checkout -f -B.
    [switch]$HubGitRepairMain,
    # On first run or minimal images: install git via apt on the hub (requires passwordless sudo).
    [switch]$HubEnsureGit,
    [string]$HubGitBranch = "main",
    # HTTPS remote for CRAVEVA hub server — use for clone / git remote origin after deploy-key or credential setup.
    [string]$HubGitRemote = "https://github.com/CRAVEVA/craveva-hub-server.git"
)

$ErrorActionPreference = "Stop"

# --- Hub SSH target & app path (must match your SSH config Host craveva-hub-server) ---
# Production docroot: aaPanel Nginx vhost hub.craveva.com must use root .../public under this same path
# (not /www/wwwroot/...). If panel UI still shows the old path, change Site directory there or this deploy will not match live HTTP.
$HubHost = "craveva-hub-server"
$HubPath = "/var/www/hub.craveva.com"
# --- GitHub source (same repo as hub production git workflow) ---
# Web: https://github.com/CRAVEVA/craveva-hub-server
# Remote URL: https://github.com/CRAVEVA/craveva-hub-server.git
$LocalTempDir = ".deploy_hub_tmp"
$ZipFile = "deploy_hub.zip"

if ($HubEnsureGit) {
    Write-Host "----------------------------------------------------------------"
    Write-Host "Step 0a: Ensure git package on $HubHost (apt install -y git)"
    Write-Host "----------------------------------------------------------------"
    ssh "${HubHost}" "sudo DEBIAN_FRONTEND=noninteractive apt-get update -qq && sudo DEBIAN_FRONTEND=noninteractive apt-get install -y git"
}

# 0) Hub server: verify Git is installed; optionally git pull (only if $HubPath is already a clone)
# Workflow: local push → GitHub (main) → hub git pull / -HubGitPull. Permission denied → chown (xem bash exit 4).
Write-Host "----------------------------------------------------------------"
Write-Host "Step 0: Git on $HubHost (path: $HubPath, remote: $HubGitRemote)"
Write-Host "  Workflow: local push → GitHub → hub pull (-HubGitPull)"
Write-Host "----------------------------------------------------------------"
$doPullFlag = if ($HubGitPull) { "1" } else { "0" }
$repairFlag = if ($HubGitRepairMain) { "1" } else { "0" }
$gitCheckScript = @'
set -euo pipefail
if ! command -v git >/dev/null 2>&1; then
  echo "ERROR: git is not installed on this server. Install: sudo apt update && sudo apt install -y git"
  exit 1
fi
echo "GIT_OK: $(git --version)"
echo "HUB_GIT_SOURCE_URL=REMOTE_PLACEHOLDER"
cd "APP_PATH_PLACEHOLDER"
if [ ! -d .git ]; then
  echo "HUB_NOT_A_GIT_REPO"
  echo "This folder was deployed by zip, not git clone. git pull will not work until you clone the repo, e.g.:"
  echo "  git clone REMOTE_PLACEHOLDER /tmp/craveva-hub-src"
  echo "Then sync .env and storage/ from the live path, or point nginx to the new clone."
  exit 2
fi
echo "HUB_IS_GIT_REPO"
git remote -v
git status -sb
BR="BRANCH_PLACEHOLDER"
OREF="origin/${BR}"
# Đảm bảo remote trỏ đúng (HTTPS deploy key / PAT đã cấu hình trên server).
current_url="$(git remote get-url origin 2>/dev/null || true)"
if [ -z "${current_url}" ]; then
  git remote add origin "REMOTE_PLACEHOLDER"
elif [ "${current_url}" != "REMOTE_PLACEHOLDER" ] && [ "${current_url}" != "REMOTE_PLACEHOLDER/" ]; then
  echo "HUB_GIT_NOTE: origin URL is ${current_url} (expected REMOTE_PLACEHOLDER) — giữ nguyên; fetch vẫn chạy."
fi
git fetch origin
if ! git rev-parse -q --verify "${OREF}" >/dev/null; then
  echo "ERROR: Không có ${OREF} sau fetch. Kiểm tra nhánh trên GitHub."
  git branch -a
  exit 3
fi
# Orphan / "No commits yet on main" / zip rồi git init — chỉ sửa khi có -HubGitPull hoặc -HubGitRepairMain (tránh ghi đè nhầm).
NEED_REPAIR=0
if [ "REPAIR_FLAG_PLACEHOLDER" = "1" ]; then
  NEED_REPAIR=1
  echo "HUB_GIT_REPAIR: forced by -HubGitRepairMain"
elif [ "DO_PULL_PLACEHOLDER" = "1" ]; then
  if ! git rev-parse -q --verify HEAD >/dev/null 2>&1; then
    NEED_REPAIR=1
    echo "HUB_GIT_REPAIR: no HEAD (empty repo)"
  else
    HC=$(git rev-list --count HEAD 2>/dev/null || echo 0)
    if [ "${HC}" -eq 0 ]; then
      NEED_REPAIR=1
      echo "HUB_GIT_REPAIR: branch has zero commits (orphan / zip deploy)"
    fi
  fi
fi
if [ "${NEED_REPAIR}" = "1" ]; then
  echo "HUB_GIT_REPAIR: git checkout -f -B ${BR} ${OREF}"
  if ! git checkout -f -B "${BR}" "${OREF}"; then
    echo "HUB_GIT_ERROR: checkout thất bại (thường do Permission denied — file thuộc www-data/root)."
    echo "Chạy một lần trên hub (user SSH = $(whoami)):"
    echo "  sudo chown -R $(whoami):$(whoami) \"$(pwd)\""
    echo "  sudo chown -R www-data:www-data \"$(pwd)/storage\" \"$(pwd)/bootstrap/cache\""
    echo "  sudo chmod -R ug+rwX \"$(pwd)/storage\" \"$(pwd)/bootstrap/cache\""
    echo "Sau đó chạy lại checkout/pull."
    exit 4
  fi
  git branch --set-upstream-to="${OREF}" "${BR}"
fi
if [ "DO_PULL_PLACEHOLDER" = "1" ]; then
  git checkout "${BR}"
  if [ "${NEED_REPAIR}" != "1" ]; then
    git branch --set-upstream-to="${OREF}" "${BR}" 2>/dev/null || true
  fi
  if ! git pull --ff-only; then
    echo "HUB_GIT_ERROR: git pull thất bại — kiểm tra quyền ghi (chown như trên) hoặc conflict."
    exit 4
  fi
  echo "GIT_PULL_DONE branch=BRANCH_PLACEHOLDER"
else
  echo "GIT_PULL_SKIPPED (add -HubGitPull to run pull; repair still ran if needed)"
fi
git status -sb
git log -1 --oneline || true
'@
$gitCheckScript = $gitCheckScript.Replace("APP_PATH_PLACEHOLDER", $HubPath).Replace("REMOTE_PLACEHOLDER", $HubGitRemote).Replace("DO_PULL_PLACEHOLDER", $doPullFlag).Replace("BRANCH_PLACEHOLDER", $HubGitBranch).Replace("REPAIR_FLAG_PLACEHOLDER", $repairFlag)

$gitCheckScript | ssh "${HubHost}" "bash -se"
$gitExit = $LASTEXITCODE
if ($gitExit -ne 0) {
    if ($gitExit -eq 2) {
        Write-Warning "Hub app path is not a git repository. Continuing with zip deploy. For git workflow, clone $HubGitRemote then align .env/storage."
    }
    elseif ($gitExit -eq 4) {
        throw "Hub Git failed: fix ownership on $HubPath (see HUB_GIT_ERROR lines from SSH — chown to your SSH user, then www-data for storage + bootstrap/cache)."
    }
    else {
        throw "Git check on hub failed (exit $gitExit)."
    }
}

Write-Host "Starting upload to $HubHost (Zip mode)..."

# 1. Clean up previous runs
if (Test-Path $LocalTempDir) { Remove-Item -Recurse -Force $LocalTempDir }
if (Test-Path $ZipFile) { Remove-Item -Force $ZipFile }

New-Item -ItemType Directory -Force -Path $LocalTempDir | Out-Null

# 2. Define files and directories to upload (Synced with Staging)
$FilesToCopy = @(
    "app/Http/Controllers/Controller.php",
    "app/Helper/start.php",
    "deploy_zipper.php",
    "database/migrations/2026_01_12_190624_add_product_custom_fields_fb.php",
    "database/migrations/2026_01_14_120000_add_additional_product_custom_fields_fb.php",
    "database/migrations/2026_01_16_000000_create_delivery_orders_table_fb.php",
    "database/migrations/2026_01_16_000001_add_delivery_order_custom_field_group.php",
    "database/migrations/2026_01_17_000003_update_delivery_order_items_add_new_columns.php",
    "database/migrations/2026_03_30_140000_remove_legacy_invoice_po_cf_seed_migrations_and_demo_fields.php",
    "Modules/Purchase/Database/Migrations/2026_01_16_130500_update_custom_field_model_for_delivery_orders.php",
    "database/migrations/2026_01_22_000000_add_manufacturing_and_expiration_date_to_purchase_stock_adjustments_table_fb.php",
    "database/migrations/2026_01_21_000000_add_storage_and_certification_to_products_table_fb.php",
    "database/migrations/2026_01_21_000001_remove_all_product_custom_fields_fb.php",
    "database/migrations/2026_01_21_000002_add_missing_import_fields_to_products_table.php",
    "database/migrations/2026_02_06_000000_add_product_custom_field_group.php",
    "database/migrations/2026_02_09_000001_ensure_product_prices_nullable.php",
    "Modules/Purchase/Resources/views/delivery-order/create.blade.php",
    "Modules/Purchase/Resources/views/delivery-order/ajax/create.blade.php",
    "Modules/Purchase/Resources/views/delivery-order/ajax/edit.blade.php",
    "Modules/Purchase/Resources/views/delivery-order/ajax/items.blade.php",
    "Modules/Purchase/Resources/views/delivery-order/pdf/delivery-order-1.blade.php",
    "Modules/Purchase/Resources/views/delivery-order/index.blade.php",
    "Modules/Purchase/Http/Controllers/DeliveryOrderController.php",
    "Modules/Purchase/DataTables/DeliveryOrderDataTable.php",
    "Modules/Purchase/Http/Controllers/PurchaseBillController.php",
    "Modules/Purchase/Http/Controllers/PurchaseOrderController.php",
    "Modules/Purchase/Entities/OrderDeliveryItem.php",
    "database/migrations/2026_01_16_000002_create_delivery_order_items_table_fb.php",
    "Modules/Purchase/Routes/web.php",
    "resources/views/sections/menu.blade.php",
    "resources/views/sections/sidebar.blade.php",
    "resources/views/layouts/app.blade.php",
    "public/js/custom.js",
    "public/css/app-custom.css",
    "Modules/LineIntegration/Routes/web.php",
    "Modules/LineIntegration/Http/Controllers/LineIntegrationController.php",
    "resources/views/import/process-form.blade.php",
    "app/Imports/ProductImport.php",
    "app/Http/Controllers/ProductController.php",
    "app/Jobs/ImportProductJob.php",
    "app/Models/CustomFieldGroup.php",
    "app/Models/DeliveryOrder.php",
    "Modules/Purchase/Entities/PurchaseStockAdjustment.php",
    "Modules/Purchase/Entities/PurchaseInventory.php",
    "Modules/Purchase/Observers/PurchaseOrderObserver.php",
    "Modules/Purchase/Http/Controllers/PurchaseInventoryController.php",
    "Modules/Purchase/DataTables/PurchaseInventoryDataTable.php",
    "Modules/Purchase/Resources/views/purchase-inventory/index.blade.php",
    "app/Http/Controllers/CustomFieldController.php",
    "resources/views/custom-fields/index.blade.php",
    "Modules/Purchase/Resources/views/purchase-inventory/ajax/create.blade.php",
    "Modules/Purchase/Resources/views/purchase-inventory/ajax/overview.blade.php",
    "Modules/Purchase/Resources/views/purchase-inventory/pdf/invoice-5.blade.php",
    "Modules/Purchase/Resources/views/purchase-inventory/ajax/add_quantity.blade.php",
    "Modules/Purchase/Resources/views/purchase-inventory/ajax/add_value.blade.php",
    "Modules/Purchase/Resources/views/purchase-inventory/pdf/invoice-1.blade.php",
    "Modules/Purchase/Http/Requests/Inventory/StorePurchaseInventoryRequest.php",
    "Modules/Purchase/Resources/lang/en/modules.php",
    "Modules/Purchase/Resources/lang/zh-TW/modules.php",
    "Modules/Purchase/Resources/lang/zh-CN/modules.php",
    "Modules/Purchase/Database/Migrations/2026_02_02_150000_setup_purchase_custom_fields_merged.php",
    "Modules/Purchase/Resources/views/purchase-inventory/ajax/import.blade.php",
    "Modules/Purchase/Resources/views/purchase-inventory/ajax/import_progress.blade.php",
    "Modules/Purchase/Imports/InventoryImport.php",
    "Modules/Purchase/Resources/lang/en/modules.php",
    "Modules/Purchase/Resources/lang/vi/modules.php",
    "Modules/Purchase/Jobs/ImportInventoryJob.php",
    "Modules/Purchase/Resources/views/purchase-products/ajax/overview.blade.php",
    "Modules/Purchase/Resources/views/purchase-order/ajax/create.blade.php",
    "Modules/Purchase/Resources/views/purchase-products/ajax/create.blade.php",
    "Modules/Purchase/Resources/views/purchase-products/ajax/update_inventory.blade.php",
    "Modules/LanguagePack/Languages/modules/Purchase/en/modules.php",
    "Modules/LanguagePack/Languages/modules/Purchase/vi/modules.php",
    "app/Http/Controllers/ProductCategoryController.php",
    "app/Http/Controllers/ProductSubCategoryController.php",
    "Modules/Purchase/Resources/views/purchase-order/ajax/edit.blade.php",
    "Modules/Purchase/Entities/PurchaseProduct.php",
    "Modules/Purchase/Http/Controllers/PurchaseProductController.php",
    "Modules/Purchase/Http/Requests/Product/UpdatePurchaseProductRequest.php",
    "app/Models/Product.php",
    "app/Jobs/ImportProductJob.php",
    "app/Http/Controllers/ImportController.php",
    "app/DataTables/ProductsDataTable.php",
    "Modules/Purchase/DataTables/PurchaseProductsDataTable.php",
    "Modules/Pricing/Routes/web.php",
    "database/migrations/2026_01_29_000000_add_purchase_and_products_to_client_modules.php",
    "resources/views/estimates/ajax/create.blade.php",
    "resources/views/estimates/ajax/edit.blade.php",
    "resources/views/super-admin/companies/ajax/edit-package.blade.php",
    "database/migrations/2026_02_02_140000_setup_pricing_module_core_merged.php",
    "app/Http/Controllers/SuperAdmin/FrontendController.php",
    "Modules/Pricing/Resources/lang/vi/app.php",
    "Modules/Performance/Resources/lang/vi/app.php",
    "Modules/Letter/Resources/lang/vi/app.php",
    "Modules/Letter/Resources/views/sections/sidebar.blade.php",
    "Modules/Pricing/Resources/views/volume_rules/ajax/create.blade.php",
    "Modules/Pricing/Resources/views/volume_rules/ajax/edit.blade.php",
    "resources/views/components/auth.blade.php",
    "resources/views/layouts/app.blade.php",
    "resources/views/tasks/ajax/create.blade.php",
    "resources/views/tasks/ajax/edit.blade.php",
    "resources/views/recurring-task/ajax/edit.blade.php",
    "resources/views/clients/ajax/create.blade.php",
    "resources/views/clients/ajax/edit.blade.php",
    "app/Http/Requests/Admin/Client/StoreClientRequest.php",
    "app/Http/Requests/Admin/Client/UpdateClientRequest.php",
    "resources/views/clients/ajax/edit.blade.php",
    "app/DataTables/SuperAdmin/CompanyDataTable.php",
    "app/Providers/RouteServiceProvider.php",
    "app/Http/Kernel.php",
    "composer.json",
    "public/vendor/sweetalert",
    "public/vendor/helper/helper.js",
    "public/js/main.js",
    "public/js/custom.js",
    "Modules/Pricing/Resources/lang/en/app.php",
    "Modules/Pricing/Resources/views/sections/sidebar.blade.php",
    "Modules/Pricing/Http/Controllers/ClientPricingController.php",
    "Modules/Pricing/Http/Controllers/ClientTierController.php",
    "Modules/Pricing/Http/Controllers/PricingController.php",
    "Modules/Pricing/Http/Controllers/PricingImportController.php",
    "Modules/Pricing/Http/Controllers/PricingTierController.php",
    "Modules/Pricing/Http/Controllers/VolumeDiscountController.php",
    "Modules/Pricing/Http/Controllers/VolumeRuleController.php",
    "Modules/Pricing/Resources/views/tiers/index.blade.php",
    "Modules/Pricing/Resources/views/tiers/ajax/create.blade.php",
    "Modules/Pricing/Resources/views/tiers/ajax/edit.blade.php",
    "Modules/Pricing/Resources/views/client_pricing/index.blade.php",
    "Modules/Pricing/Resources/views/client_tiers/index.blade.php",
    "Modules/Pricing/Resources/views/client_tiers/edit.blade.php",
    "Modules/Pricing/Resources/views/client_tiers/ajax/edit.blade.php",
    "Modules/Pricing/Http/Controllers/ClientPricingController.php",
    "Modules/Pricing/Http/Controllers/CompanyPricingController.php",
    "Modules/Pricing/Resources/views/company_pricing/index.blade.php",
    "Modules/Pricing/Resources/views/company_pricing/create.blade.php",
    "Modules/Pricing/Resources/views/company_pricing/edit.blade.php",
    "Modules/Pricing/Resources/views/company_pricing/ajax/create.blade.php",
    "Modules/Pricing/Resources/views/company_pricing/ajax/edit.blade.php",
    "Modules/Pricing/Routes/web.php",
    "Modules/Pricing/Resources/views/volume_rules/index.blade.php",
    "Modules/Pricing/Resources/views/volume_rules/ajax/create.blade.php",
    "Modules/Pricing/Resources/views/volume_rules/ajax/edit.blade.php",
    "Modules/Warehouse/Resources/lang/vi/app.php",
    "Modules/Warehouse/Entities/Warehouse.php",
    "Modules/Warehouse/Entities/WarehouseProductStock.php",
    "Modules/Warehouse/Database/Migrations/2026_01_19_083640_create_warehouses_table.php",
    "Modules/Warehouse/Database/Migrations/2026_01_19_083641_create_warehouse_product_stock_table.php",
    "Modules/Biometric/Resources/lang/vi/app.php",
    "Modules/Purchase/DataTables/PurchaseInventoryDataTable.php",
    "Modules/Purchase/Resources/views/purchase-inventory/index.blade.php",
    "app/Http/Controllers/ProductController.php",
    "Modules/Pricing/Resources/views/client_pricing/ajax/edit.blade.php",
    "Modules/Pricing/Resources/views/client_pricing/ajax/create.blade.php",
    "Modules/Pricing/Services/PricingService.php",
    "Modules/Pricing/Services/VolumeDiscountService.php",
    "resources/views/layouts/app.blade.php",
    "resources/views/layouts/quill-script-include.blade.php",
    "resources/views/components/menu-item.blade.php",
    "resources/views/components/sub-menu-item.blade.php",
    "app/View/Components/SubMenuItem.php",
    "resources/views/components/auth.blade.php",
    "public/vendor/sweetalert/sweetalert2.all.min.js",
    "public/vendor/sweetalert/sweetalert2.min.css"
)

$FilesToCopy += @(
    "app/DataTables/TasksDataTable.php",
    "resources/views/tasks/index.blade.php",
    "app/Http/Controllers/TaskController.php",
    "app/Http/Controllers/CustomFieldController.php",
    "routes/web-settings.php",
    "resources/views/custom-fields/index.blade.php",
    "database/migrations/2026_01_22_092609_add_sort_order_to_custom_fields_table.php",
    "resources/views/components/forms/custom-field.blade.php",
    "resources/views/components/forms/custom-field-show.blade.php",
    "resources/views/custom-fields/create-custom-field-modal.blade.php",
    "resources/views/super-admin/companies/ajax/show.blade.php",
    "resources/scss/sidebar.scss",
    "public/css/app.css",
    "resources/views/layouts/app.blade.php",
    "public/vendor/bootstrap-select/js/bootstrap-select.min.js",
    "public/vendor/bootstrap-select/css/bootstrap-select.min.css",
    "public/js/main.js",
    "public/js/custom.js",
    "public/vendor/datatables/buttons.colVis.min.js",
    "resources/views/sections/datatable_js.blade.php"
)

$FilesToCopy += @(
    "app/Http/Controllers/SuperAdmin/BillingController.php",
    "app/Observers/CompanyObserver.php",
    "app/Http/Controllers/SuperAdmin/PackageController.php",
    "app/Http/Controllers/SuperAdmin/OfflinePlanChangeController.php",
    "app/Console/Commands/SuperAdmin/LicenceExpire.php",
    "app/Console/Commands/SuperAdmin/TrialExpire.php",
    "resources/views/super-admin/invoices/pdf/invoice-1.blade.php",
    "resources/views/super-admin/invoices/pdf/invoice-2.blade.php",
    "resources/views/super-admin/invoices/pdf/invoice-3.blade.php",
    "resources/views/super-admin/invoices/pdf/invoice-4.blade.php",
    "resources/views/super-admin/invoices/pdf/invoice-5.blade.php",
    "app/Http/Controllers/SuperAdmin/StripeWebhookController.php",
    "app/Console/Commands/AutoCreateRecurringInvoices.php",
    "app/DataTables/ClientsDataTable.php",
    "app/Models/ClientDetails.php",
    "webpack.mix.js",
    "Modules/Onboarding/Database/Migrations/2024_01_01_00001_create_onboarding_settings_table.php",
    "Modules/Policy/Database/Seeders/PolicyCentreDatabaseSeeder.php"
)

$FilesToCopy += @(
    "resources/views/leads/ajax/edit.blade.php",
    "resources/views/products/ajax/create.blade.php",
    "resources/views/products/ajax/edit.blade.php",
    "resources/views/lead-contact/ajax/edit.blade.php",
    "resources/views/expenses/ajax/edit.blade.php",
    "resources/views/contracts/ajax/edit.blade.php",
    "resources/views/clients/contacts/edit.blade.php",
    "resources/views/clients/ajax/edit.blade.php",
    "resources/views/invoices/ajax/edit.blade.php",
    "resources/views/employees/ajax/edit.blade.php",
    "resources/views/timelogs/ajax/edit.blade.php",
    "resources/views/recurring-invoices/edit.blade.php",
    "resources/views/recurring-expenses/ajax/edit.blade.php",
    "resources/views/projects/ajax/edit.blade.php",
    "resources/views/recurring-task/ajax/create.blade.php",
    "resources/views/recurring-task/ajax/edit.blade.php",
    "resources/views/recurring-task/index.blade.php",
    "resources/views/tasks/create.blade.php",
    "resources/views/tasks/ajax/create.blade.php",
    "resources/views/tasks/ajax/edit.blade.php",
    "resources/views/tasks/ajax/sub_tasks.blade.php",
    "resources/views/tasks/sub_tasks/edit.blade.php",
    "resources/views/estimates/ajax/edit.blade.php",
    "resources/views/sections/menu.blade.php",
    "resources/views/products/ajax/cart.blade.php",
    "Modules/Purchase/Resources/views/purchase-products/ajax/edit.blade.php",
    "Modules/Purchase/Resources/views/purchase-products/index.blade.php",
    "Modules/Purchase/Resources/views/purchase-products/ajax/import.blade.php",
    "Modules/Pricing/DataTables/ClientTiersDataTable.php",
    "Modules/Pricing/Resources/views/client_pricing/index.blade.php",
    "Modules/Pricing/Resources/views/company_pricing/index.blade.php",
    "Modules/Pricing/Resources/lang/en/app.php",
    "Modules/Pricing/Resources/lang/vi/app.php",
    "app/Http/Controllers/UpdateAppController.php",
    "app/Http/Requests/UploadInstallRequest.php",
    "resources/views/custom-modules/install.blade.php",
    "resources/views/tasks/ajax/show.blade.php",
    "resources/views/tasks/comments/edit.blade.php",
    "resources/views/tasks/waiting-approval.blade.php"
)

$FilesToCopy += @(
    "app/Http/Controllers/CustomFieldController.php",
    "resources/views/custom-fields/index.blade.php",
    "routes/web-settings.php",
    "resources/views/custom-fields/create-custom-field-modal.blade.php",
    "app/Traits/ImportExcel.php",
    "app/Traits/CustomFieldsTrait.php",
    "app/Models/Product.php",
    "app/Models/ProductCategory.php",
    "app/Models/ProductSubCategory.php",
    "resources/views/theme-settings/ajax/cropper.blade.php",
    "resources/views/super-admin/theme-settings/ajax/cropper.blade.php",
    "public/vendor/cropper/cropper.min.css",
    "public/vendor/cropper/cropper.min.js",
    "tests/Feature/ChatboxTest.php",
    "tests/Feature/ChatboxToggleTest.php",
    "Modules/Pricing/Database/Migrations/2026_02_11_121332_add_start_and_end_date_to_client_product_pricing_table.php",
    "Modules/Pricing/docs/UserGuide.md",
    "Modules/Pricing/docs/ReleaseNotes.md",
    "Modules/Pricing/Tests/Unit/ContractPricingTest.php"
)

$DirsToCopy = @(
    "Modules/LanguagePack/Languages/app",
    "Modules/LanguagePack/Languages/modules",
    "Modules/Pricing",
    "Modules/Purchase",
    "Modules/Performance/Resources/lang",
    "public/vendor/dropify",
    "public/vendor/quill",
    "app/Imports",
    "app/Jobs",
    "resources/views/products"
)

# 3. Copy files to temp dir
Write-Host "Preparing files..."
foreach ($File in $FilesToCopy) {
    if (Test-Path $File) {
        $Dest = Join-Path $LocalTempDir $File
        $Parent = Split-Path $Dest
        if (-not (Test-Path $Parent)) { New-Item -ItemType Directory -Force -Path $Parent | Out-Null }
        Copy-Item $File $Dest
    }
    else {
        Write-Warning "File not found: $File"
    }
}

foreach ($Dir in $DirsToCopy) {
    if (Test-Path $Dir) {
        $Dest = Join-Path $LocalTempDir $Dir
        $Parent = Split-Path $Dest
        if (-not (Test-Path $Parent)) { New-Item -ItemType Directory -Force -Path $Parent | Out-Null }
        Copy-Item -Recurse -Force $Dir $Parent -Exclude ".gitignore",".git"
    }
    else {
        Write-Warning "Directory not found: $Dir"
    }
}

# Wait for file handles to release
Start-Sleep -Seconds 2

# Verify critical files in temp
$CriticalFiles = @(
    "Modules/Pricing/Http/Controllers/CompanyPricingController.php",
    "Modules/Pricing/module.json",
    "Modules/Pricing/Routes/web.php"
)

Write-Host "Verifying critical files in temp directory..."
foreach ($CFile in $CriticalFiles) {
    $CPath = Join-Path $LocalTempDir $CFile
    if (Test-Path $CPath) {
        Write-Host "[OK] Found $CFile"
    } else {
        Write-Error "[MISSING] Could not find $CFile in temp directory!"
        Get-ChildItem -Recurse $LocalTempDir | Select-Object FullName
        exit 1
    }
}

# 4. Zip files
Write-Host "Verifying Modules in temp..."
Get-ChildItem "$LocalTempDir\Modules" | Select-Object Name

Write-Host "Compressing files using PHP (to ensure forward slashes)..."
php deploy_zipper.php $LocalTempDir $ZipFile

# 5. Upload Zip
Write-Host "Zip ready: $ZipFile"
# exit 0
Write-Host "Uploading zip package to home directory (~/$ZipFile)..."
scp $ZipFile "${HubHost}:$ZipFile"

# 6. Backup hub source on server, then extract and deploy (sudo may be required)
Write-Host "Backup hub source on server, then extract and deploy to $HubPath..."
$BackupDir = "~/hub_backups"
# Backup filename is evaluated on the server (bash); use single-quoted literal so PowerShell does not expand $(date ...)
$BackupNameLiteral = 'hub_backup_$(date +%Y%m%d_%H%M%S).tar.gz'
$RemoteCommand = "mkdir -p $BackupDir && cd /var/www && sudo tar -czf $BackupDir/$BackupNameLiteral hub.craveva.com && echo 'Backup done'"
$RemoteCommand += " && sudo mv ~/$ZipFile $HubPath/$ZipFile && cd $HubPath"
# Debug: List zip content for the controller
$RemoteCommand += " && echo 'Checking zip content for CompanyPricingController...'"
$RemoteCommand += " && unzip -l $ZipFile | grep CompanyPricingController.php || echo 'NOT IN ZIP'"
# Safety: Remove existing Pricing module to ensure clean extract
$RemoteCommand += " && echo 'Removing existing Pricing module directory...'"
$RemoteCommand += " && sudo rm -rf Modules/Pricing"
# Unzip
$RemoteCommand += " && sudo unzip -o $ZipFile && sudo rm $ZipFile"
# Fix permissions (safe defaults for Laravel + future git pull)
# - Code tree: deploy user owns files, web server group can read.
# - Runtime writable dirs: www-data owns storage + bootstrap/cache.
$RemoteCommand += " && sudo chown -R hoangphat5393:www-data $HubPath"
$RemoteCommand += " && sudo find $HubPath -path $HubPath/storage -prune -o -path $HubPath/bootstrap/cache -prune -o -type d -exec chmod 755 {} \\;"
$RemoteCommand += " && sudo find $HubPath -path $HubPath/storage -prune -o -path $HubPath/bootstrap/cache -prune -o -type f -exec chmod 644 {} \\;"
$RemoteCommand += " && sudo mkdir -p $HubPath/storage $HubPath/bootstrap/cache"
$RemoteCommand += " && sudo chown -R www-data:www-data $HubPath/storage $HubPath/bootstrap/cache"
$RemoteCommand += " && sudo find $HubPath/storage $HubPath/bootstrap/cache -type d -exec chmod 2775 {} \\;"
$RemoteCommand += " && sudo find $HubPath/storage $HubPath/bootstrap/cache -type f -exec chmod 664 {} \\;"
$RemoteCommand += " && [ -f $HubPath/.env ] && sudo chmod 640 $HubPath/.env || true"
$RemoteCommand += " && [ -f $HubPath/.env.backupv2 ] && sudo chmod 640 $HubPath/.env.backupv2 || true"
$RemoteCommand += " && [ -f $HubPath/env.production ] && sudo chmod 640 $HubPath/env.production || true"
# Clear caches and run migrations
$RemoteCommand += " && sudo -u www-data php artisan migrate --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=database/migrations/2026_01_21_000000_add_storage_and_certification_to_products_table_fb.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=Modules/Purchase/Database/Migrations/2026_02_02_150000_setup_purchase_custom_fields_merged.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=Modules/Pricing/Database/Migrations/2026_02_02_160000_setup_pricing_module_permissions_and_activation.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=Modules/Pricing/Database/Migrations/2026_02_11_121332_add_start_and_end_date_to_client_product_pricing_table.php --force"
$RemoteCommand += " && sudo -u www-data php artisan migrate --path=database/migrations/2026_02_02_140000_setup_pricing_module_core_merged.php --force"
# $RemoteCommand += " && sudo -u www-data composer dump-autoload"
$RemoteCommand += " && sudo -u www-data php artisan module:enable Pricing"
$RemoteCommand += " && sudo -u www-data php artisan optimize:clear"
$RemoteCommand += " && echo 'Running check_pricing_v2.php...'"
$RemoteCommand += " && sudo -u www-data php check_pricing_v2.php"

ssh "${HubHost}" $RemoteCommand

# 7. Local Cleanup
Remove-Item -Recurse -Force $LocalTempDir
Remove-Item -Force $ZipFile

Write-Host "----------------------------------------------------------------"
Write-Host "Upload and deployment to $HubHost complete!"
Write-Host "----------------------------------------------------------------"
