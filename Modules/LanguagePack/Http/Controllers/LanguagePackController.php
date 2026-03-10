<?php

namespace Modules\LanguagePack\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\LanguageSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Modules\LanguagePack\Http\Requests\PublishLanguageRequest;
use Symfony\Component\Console\Output\BufferedOutput;

class LanguagePackController extends AccountBaseController
{
    public function publishAll()
    {
        $languages = LanguageSetting::all();

        try {
            foreach ($languages as $language) {
                $this->publishLanguage($language->language_code);
            }
        } catch (\Throwable $th) {
            $message = $this->formatPublishErrorMessage($th);
            return Reply::error($message);
        }

        return Reply::success(__('languagepack::messages.allLanguagePublished'));
    }

    public function publish(PublishLanguageRequest $request)
    {
        try {
            $this->publishLanguage($request->languageCode);
        } catch (\Throwable $th) {
            $message = $this->formatPublishErrorMessage($th);
            return Reply::error($message);
        }

        return Reply::success(__('languagepack::messages.languagePublished'));
    }

    /**
     * Run languagepack:sync-keys and return the command output.
     */
    public function syncKeys()
    {
        try {
            $output = new BufferedOutput;
            Artisan::call('languagepack:sync-keys', [], $output);
            $message = trim($output->fetch());

            return Reply::success(
                $message ?: __('languagepack::messages.syncKeysCompleted')
            );
        } catch (\Throwable $th) {
            return Reply::error($th->getMessage());
        }
    }

    private function publishLanguage($languageCode)
    {
        $path = lang_path($languageCode);

        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        $sourcePath = languagePackPath($languageCode);

        if (File::isDirectory($sourcePath)) {
            File::ensureDirectoryExists($path);
            File::copyDirectory($sourcePath, $path);
        }

        $modules = \Nwidart\Modules\Facades\Module::all();

        foreach ($modules as $moduleName => $module) {
            $this->publishModuleLanguage($moduleName, $languageCode);
        }
    }

    /**
     * Format permission/IO errors so user knows how to fix (e.g. run from CLI or fix folder permissions).
     */
    private function formatPublishErrorMessage(\Throwable $th): string
    {
        $msg = $th->getMessage();
        if (str_contains($msg, 'Permission denied') || str_contains($msg, 'Failed to open stream')) {
            return $msg . ' ' . __('languagepack::messages.publishPermissionHint');
        }

        return $msg;
    }

    private function publishModuleLanguage($module, $languageCode)
    {
        $path = module_path($module, 'Resources/lang/' . $languageCode);

        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        $sourcePath = languagePackPath($languageCode, $module);

        if (File::isDirectory($sourcePath)) {
            File::ensureDirectoryExists($path);
            File::copyDirectory($sourcePath, $path);
        }
    }
}
