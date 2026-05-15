<?php

namespace App\Console\Commands;

use App\Models\Module as AppModule;
use App\Models\SuperAdmin\Package;
use App\Observers\CompanyObserver;
use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module as NwidartModule;

class PackageModulesCommand extends Command
{
    protected $signature = 'packages:modules
                            {action : list | activate-all | activate | enable-custom | activate-all-full }
                            {--package= : Package ID (optional; for activate-all/activate, default: all packages)}
                            {--module= : Module name (required for action=activate)}';

    protected $description = 'List/activate package modules (list|activate-all|activate) or enable Custom Modules toggles (enable-custom). Use activate-all-full to do both.';

    public function handle(): int
    {
        $action = strtolower($this->argument('action'));

        if ($action === 'activate-all-full') {
            return $this->runActivateAllFull();
        }

        if ($action === 'enable-custom') {
            return $this->runEnableCustom();
        }

        if (! in_array($action, ['list', 'activate-all', 'activate'], true)) {
            $this->error('Action phải là: list | activate-all | activate | enable-custom | activate-all-full');

            return self::FAILURE;
        }

        $allModuleNames = $this->getAllPackageModuleNames();

        if ($action === 'list') {
            return $this->runList($allModuleNames);
        }

        if ($action === 'activate-all') {
            return $this->runActivateAll($allModuleNames);
        }

        if ($action === 'activate') {
            $module = $this->option('module');
            if (empty($module)) {
                $this->error('Với action=activate cần truyền --module=tên_module (vd: --module=clients)');

                return self::FAILURE;
            }
            $module = strtolower(trim($module));
            if (! in_array($module, $allModuleNames, true)) {
                $this->warn("Module \"{$module}\" không nằm trong danh sách module gói. Danh sách: ".implode(', ', $allModuleNames));
            }

            return $this->runActivateOne($module, $allModuleNames);
        }

        return self::SUCCESS;
    }

    /**
     * Bật cả package modules (activate-all) và Custom Modules (enable-custom).
     * Dùng khi muốn một lệnh xong: gói đủ module + trang Custom Modules toàn bộ toggle ON.
     */
    protected function runActivateAllFull(): int
    {
        $this->info('Bước 1/2: Bật toàn bộ module trong Package và đồng bộ module_settings...');
        $allModuleNames = $this->getAllPackageModuleNames();
        if ($this->runActivateAll($allModuleNames) !== self::SUCCESS) {
            return self::FAILURE;
        }
        $this->newLine();
        $this->info('Bước 2/2: Bật toàn bộ Custom Modules (toggle trang Module Settings)...');

        return $this->runEnableCustom();
    }

    /**
     * Bật toàn bộ Custom Modules (trang Settings > Module Settings > Custom Modules).
     * Ghi vào storage/app/modules_statuses.json và xóa cache — toggle trên UI sẽ hiển thị ON.
     */
    protected function runEnableCustom(): int
    {
        $collection = NwidartModule::toCollection();
        $enabled = 0;
        foreach ($collection as $key => $item) {
            $name = is_string($item) ? $item : $key;
            $module = NwidartModule::find($name);
            if (! $module || ! method_exists($module, 'enable')) {
                continue;
            }
            if ($module->isEnabled()) {
                $this->line("  [đã bật] {$name}");

                continue;
            }
            $module->enable();
            $this->info("  Đã bật: {$name}");
            $enabled++;
        }
        cache()->forget('craveva_plugins');
        cache()->forget('user_modules');
        /** @phpstan-ignore-next-line */
        cache()->put('craveva_plugins', array_keys(NwidartModule::allEnabled()));
        $this->info("Xong. Đã bật {$enabled} custom module. Reload trang Module Settings để thấy toggle ON.");

        return self::SUCCESS;
    }

    /**
     * Danh sách module dùng cho gói (trùng logic PackageController / PackageDataTable).
     */
    protected function getAllPackageModuleNames(): array
    {
        return AppModule::where('module_name', '<>', 'settings')
            ->where('module_name', '<>', 'dashboards')
            ->where('module_name', '<>', 'restApi')
            ->where('module_name', '<>', 'discount')
            ->whereNotIn('module_name', AppModule::disabledModuleArray())
            ->orderBy('module_name')
            ->pluck('module_name')
            ->values()
            ->all();
    }

    protected function runList(array $allModuleNames): int
    {
        $this->info('Danh sách module dùng cho Package (từ bảng modules, trừ settings/dashboards/restApi/disabled):');
        $this->line(implode(', ', $allModuleNames));
        $this->newLine();

        $packages = Package::orderBy('sort')->orderBy('id')->get();

        if ($packages->isEmpty()) {
            $this->warn('Chưa có package nào.');

            return self::SUCCESS;
        }

        foreach ($packages as $pkg) {
            $inPackage = $this->getPackageModuleNames($pkg);
            $missing = array_diff($allModuleNames, $inPackage);
            $this->info("Package #{$pkg->id} — {$pkg->name} (default={$pkg->default})");
            $this->line('  Trong gói: '.count($inPackage).' — '.implode(', ', $inPackage));
            if (count($missing) > 0) {
                $this->warn('  Thiếu: '.implode(', ', $missing));
            }
            $this->newLine();
        }

        return self::SUCCESS;
    }

    protected function getPackageModuleNames(Package $package): array
    {
        $decoded = json_decode($package->module_in_package, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($value) => strtolower(trim((string) $value)),
            $decoded
        ))));
    }

    protected function runActivateAll(array $allModuleNames): int
    {
        $packageId = $this->option('package');
        $packages = $packageId
            ? Package::where('id', (int) $packageId)->get()
            : Package::orderBy('sort')->orderBy('id')->get();

        if ($packages->isEmpty()) {
            $this->error($packageId ? "Không tìm thấy package id={$packageId}." : 'Chưa có package nào.');

            return self::FAILURE;
        }

        $modulesJson = json_encode(array_values($allModuleNames));
        $observer = app(CompanyObserver::class);

        foreach ($packages as $pkg) {
            $pkg->module_in_package = $modulesJson;
            $pkg->save();
            $this->info("Đã cập nhật package #{$pkg->id} ({$pkg->name}): bật toàn bộ ".count($allModuleNames).' module.');

            foreach ($pkg->companies as $company) {
                $observer->updateModuleSettings($company);
            }
            $this->line('  Đồng bộ module_settings cho '.$pkg->companies->count().' company.');
        }

        $this->info('Xong.');

        return self::SUCCESS;
    }

    protected function runActivateOne(string $moduleName, array $allModuleNames): int
    {
        $packageId = $this->option('package');
        $packagesQuery = Package::with('companies')->orderBy('sort')->orderBy('id');
        $packages = $packageId
            ? $packagesQuery->where('id', (int) $packageId)->get()
            : $packagesQuery->get();

        if ($packages->isEmpty()) {
            $this->error($packageId ? "Không tìm thấy package id={$packageId}." : 'Chưa có package nào.');

            return self::FAILURE;
        }

        $observer = app(CompanyObserver::class);
        $updated = 0;

        foreach ($packages as $pkg) {
            $inPackage = $this->getPackageModuleNames($pkg);
            if (in_array($moduleName, $inPackage, true)) {
                $this->line("Package #{$pkg->id} ({$pkg->name}): đã có module \"{$moduleName}\" — đồng bộ module_settings cho company.");
                foreach ($pkg->companies as $company) {
                    $observer->updateModuleSettings($company);
                }

                continue;
            }
            $inPackage[] = $moduleName;
            $inPackage = array_values(array_unique($inPackage));
            $pkg->module_in_package = json_encode($inPackage);
            $pkg->save();
            $updated++;
            $this->info("Đã thêm module \"{$moduleName}\" vào package #{$pkg->id} ({$pkg->name}).");

            foreach ($pkg->companies as $company) {
                $observer->updateModuleSettings($company);
            }
        }

        if ($updated > 0) {
            $this->info("Đã cập nhật {$updated} package và đồng bộ module_settings.");
        }

        return self::SUCCESS;
    }
}
