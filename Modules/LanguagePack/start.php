<?php

use Illuminate\Support\Facades\File;
use Modules\LanguagePack\Entities\LanguagePackSetting;

if (! function_exists('languagePackSetting')) {

    function languagePackSetting()
    {
        if (! session()->has('languagePackSetting') || ! session('languagePackSetting')) {
            session(['languagePackSetting' => LanguagePackSetting::first()]);
        }

        return session('languagePackSetting');
    }
}

if (! function_exists('languagePackPath')) {

    function languagePackPath($languageCode, $module = null)
    {

        if ($module) {
            return module_path('LanguagePack', 'Languages/modules/'.$module.'/'.$languageCode);
        }

        return module_path('LanguagePack', 'Languages/app/'.$languageCode);
    }
}

if (! function_exists('isLanguagePackAvailable')) {

    function isLanguagePackAvailable($languageCode)
    {

        $path = languagePackPath($languageCode);

        return File::isDirectory($path);
    }
}

if (! function_exists('isModuleLanguagePackAvailable')) {

    function isModuleLanguagePackAvailable($module, $languageCode)
    {
        $path = languagePackPath($languageCode, $module);

        return File::isDirectory($path);
    }
}

if (! function_exists('isLanguagePublished')) {

    function isLanguagePublished($languageCode)
    {
        $path = lang_path($languageCode);

        if (! File::isDirectory($path)) {
            return false;
        }

        // Published when directory contains files (isEmptyDirectory not in all Laravel versions)
        return (new \FilesystemIterator($path))->valid();
    }
}
