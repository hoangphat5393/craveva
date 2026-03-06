<?php

namespace App\Http\Controllers;

use App\Events\ModuleStatusChanged;
use App\Helper\Reply;
use App\Models\GlobalSetting;
use App\Models\ModuleSetting;
use Froiden\Envato\Functions\EnvatoUpdate;
use Froiden\Envato\Traits\ModuleVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Macellan\Zip\Zip;
use Nwidart\Modules\Facades\Module;

class CustomModuleController extends AccountBaseController
{
    use ModuleVerify;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.moduleSettings';
        $this->activeSettingMenu = 'module_settings';
        $this->middleware(function ($request, $next) {
            abort_403(GlobalSetting::validateSuperAdmin('manage_superadmin_custom_module_settings'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->type = 'custom';
        $this->updateFilePath = storage_path().'/app';
        /** @phpstan-ignore-next-line */
        $this->allModules = Module::toCollection();

        $this->view = 'custom-modules.ajax.custom';
        $this->activeTab = 'custom';
        $this->plugins = collect(EnvatoUpdate::plugins());

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('module-settings.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->pageTitle = 'app.menu.moduleSettingsInstall';
        $this->type = 'custom';
        $this->updateFilePath = storage_path().'/app';

        return view('custom-modules.install', $this->data);
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function store(Request $request)
    {
        if (! extension_loaded('zip')) {
            return Reply::error('<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.');
        }

        File::put(public_path().'/install-version.txt', 'complete');

        $filePath = $request->filePath;

        $this->removeGitkeepFilesFromZip($filePath);
        $modulesTempPath = storage_path('app').'/Modules';

        if (File::isDirectory($modulesTempPath)) {
            File::deleteDirectory($modulesTempPath);
        }

        $zipArchive = new \ZipArchive;

        if ($zipArchive->open($filePath) === true) {
            @$zipArchive->extractTo($modulesTempPath);
            $zipArchive->close();
        } else {
            return Reply::error('Unable to open module zip file.');
        }

        // Find config.php to determine module name and location
        $files = File::allFiles($modulesTempPath);
        $configPath = null;

        // Debug: List files for troubleshooting
        $foundFiles = [];
        foreach ($files as $file) {
            $foundFiles[] = $file->getPathname();
            // Case-insensitive check and normalize separators
            $normalizedPath = str_replace('\\', '/', $file->getPathname());
            if (str_contains(strtolower($normalizedPath), '/config/config.php')) {
                $configPath = $file->getPathname();
                break;
            }
        }

        if (! $configPath) {
            // Return first 5 files found to help debug
            $fileList = implode(', ', array_slice($foundFiles, 0, 5));

            return Reply::error('Config/config.php not found in the zip file. Found files: '.$fileList);
        }

        $config = require $configPath;
        $moduleName = $config['name'] ?? null;

        if (! $moduleName) {
            return Reply::error('Module name is missing in Config/config.php.');
        }

        $currentModuleRoot = dirname(dirname($configPath));
        $targetModuleRoot = $modulesTempPath.'/'.$moduleName;

        if ($currentModuleRoot === $modulesTempPath) {
            // Files are at root level of extraction
            File::makeDirectory($targetModuleRoot, 0755, true);

            // Move directories
            foreach (File::directories($modulesTempPath) as $dir) {
                if ($dir !== $targetModuleRoot) {
                    File::moveDirectory($dir, $targetModuleRoot.'/'.basename($dir), true);
                }
            }

            // Move files
            foreach (File::files($modulesTempPath) as $file) {
                File::move($file->getPathname(), $targetModuleRoot.'/'.$file->getFilename());
            }
        } elseif ($currentModuleRoot !== $targetModuleRoot) {
            if (File::exists($targetModuleRoot)) {
                File::deleteDirectory($targetModuleRoot);
            }
            File::moveDirectory($currentModuleRoot, $targetModuleRoot, true);
        }

        $validateModule = $this->validateModule($moduleName);

        if ($validateModule['status'] == true) {
            // Move files to Modules if modules belongs to this product
            File::moveDirectory(storage_path('app').'/Modules/'.$moduleName, base_path().'/Modules/'.$moduleName, true);

            cache()->forget('laravel-modules');

            // Delete Modules Directory after moving files
            File::deleteDirectory(storage_path('app').'/Modules/');

            if (module_enabled($moduleName)) {
                $this->updateVersion($moduleName);
            }

            $this->flushData();

            return Reply::success('Installed successfully.');
        }

        return Reply::error($validateModule['message']);
    }

    private function removeGitkeepFilesFromZip($filePath)
    {
        $zipArchive = new \ZipArchive;

        if ($zipArchive->open($filePath) === true) {
            for ($i = $zipArchive->numFiles - 1; $i >= 0; $i--) {
                $stat = $zipArchive->statIndex($i);

                if ($stat && basename($stat['name']) === '.gitkeep') {
                    $zipArchive->deleteIndex($i);
                }
            }

            $zipArchive->close();
        }
    }

    public function validateModule($moduleName)
    {
        $appName = str_replace('-new', '', config('craveva.product_name'));
        $wrongMessage = 'The zip that you are trying to install is not compatible with '.$appName.' version';

        // Check if PHP-ZIP extension is missing
        if (! extension_loaded('zip')) {
            return [
                'status' => false,
                'message' => '<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.',
            ];
        }

        $configPath = storage_path('app').'/Modules/'.$moduleName.'/Config/config.php';

        // Check if module configuration file exists
        if (! file_exists($configPath)) {
            return [
                'status' => false,
                'message' => $wrongMessage,
            ];
        }

        $config = require_once $configPath;

        // Check if parent_min_version is defined
        if (! isset($config['parent_min_version'])) {
            $errorMessage = 'Minimum version of <b>'.$appName.' main application</b> is not defined in the Module.';

            return [
                'status' => false,
                'message' => $errorMessage,
            ];
        }

        // Check if the application version is lower than the required minimum version
        if ($config['parent_min_version'] >= File::get('version.txt')) {
            return [
                'status' => false,
                'message' => 'Minimum version of <b>'.$appName.' main application</b> should be greater than or equal to <b>'.$config['parent_min_version'].'</b>. Your application version is <b>'.File::get('version.txt').'</b>',
            ];
        }

        return [
            'status' => true,
            'message' => 'Unzipped successfully',
        ];
    }

    private function flushData()
    {
        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        $user = auth()->id();
        // clear cache
        cache()->flush();
        // clear session
        session()->flush();
        auth()->logout();
        // login user
        auth()->loginUsingId($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        return $this->verifyModulePurchase($id);
    }

    public function update(Request $request, $moduleName)
    {
        /** @phpstan-ignore-next-line */
        $module = Module::findOrFail($moduleName);

        $status = $request->status;

        ModuleSetting::where('module_name', $moduleName)->delete();

        ($status == 'active') ? $module->enable() : $module->disable();

        event(new ModuleStatusChanged($moduleName, $status));

        // We are registering the module to run the commands
        $module->register();

        /** @phpstan-ignore-next-line */
        $plugins = \Nwidart\Modules\Facades\Module::allEnabled();

        if ($status == 'active') {
            $this->runModuleMigrateCommand($moduleName);

            // We will call the module function php artisan asset:activate, zoom:active , etc
            $this->runActivateCommand(strtolower($moduleName));
        }

        $this->flushData();

        if (strtolower($moduleName) == 'subdomain' && ($status == 'active')) {
            \session(['subdomain_module_activated' => true]);
        }

        cache()->forget('user_modules');
        /** @phpstan-ignore-next-line */
        cache(['craveva_plugins' => array_keys($plugins)]);

        if (strtolower($moduleName) == 'languagepack' && $status == 'active') {
            session(['languagepack_module_activated' => true]);
        }

        return Reply::redirect(route('custom-modules.index').'?tab=custom', 'Status Changed. Reloading');
    }

    public function verifyingModulePurchase(Request $request)
    {
        $request->validate([
            'purchase_code' => 'required|max:80',
        ]);

        $module = $request->module;
        $purchaseCode = $request->purchase_code;

        return $this->modulePurchaseVerified($module, $purchaseCode);
    }

    private function getZipName($filePath)
    {
        $array = explode('/', str_replace('\\', '/', $filePath));

        return end($array);
    }

    /**
     * @param  $moduleName
     *                     This will update the version of on server
     */
    private function updateVersion($moduleName)
    {
        try {
            $config = require base_path().'/Modules/'.$moduleName.'/Config/config.php';
            $setting = (new $config['setting'])::first();

            // When module migrations are not run

            if ($setting?->purchase_code) {
                $this->modulePurchaseVerified(strtolower($moduleName), $setting->purchase_code);
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }
    }

    private function runModuleMigrateCommand($moduleName)
    {
        Artisan::call('module:migrate', [$moduleName, '--force' => true]);
    }

    private function runActivateCommand($moduleName)
    {
        $command = $moduleName.':activate';

        $artisanCommands = Artisan::all();

        if (array_has($artisanCommands, $command)) {
            Artisan::call($command);
        }
    }
}
