<?php

namespace App\Helper;

use App\Models\Company;
use App\Models\FileStorage;
use App\Models\StorageSetting;
use Exception;
use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Files
{
    const UPLOAD_FOLDER = 'user-uploads';

    const IMPORT_FOLDER = 'import-files';

    const REQUIRED_FILE_UPLOAD_SIZE = 20;

    /**
     * @param  mixed  $image
     * @param  null  $width
     * @return string
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    public static function upload($image, string $dir, $width = null, int $height = 800)
    {
        // To upload files to local server
        config(['filesystems.default' => 'local']);

        $uploadedFile = $image;
        $folder = trim($dir, '/\\');

        self::validateUploadedFile($uploadedFile);

        $realPath = $uploadedFile->getRealPath();
        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());
        if ($newName === '') {
            $newName = md5(microtime()).'.xlsx';
        }

        $tempPath = public_path(self::UPLOAD_FOLDER.'/temp/'.$newName);

        /** Check if folder exists or not. If not then create the folder (temp + target dir for staging) */
        self::createDirectoryIfNotExist('temp');
        self::createDirectoryIfNotExist($folder);

        $newPath = ($folder !== '' ? ($folder.'/') : '').$newName;
        $tempRelative = 'temp/'.$newName;

        // On Windows or some PHP configs getRealPath() can return false even when file is valid; fallback to writing content to temp
        if ($realPath === false || $realPath === '') {
            $content = $uploadedFile->get();
            if ($content === null || $content === '') {
                $msg = __('messages.pleaseSelectFile');
                if ($msg === 'messages.pleaseSelectFile') {
                    $msg = 'Please select a valid file to upload.';
                }
                throw new Exception($msg);
            }
            File::put($tempPath, $content);
        } else {
            $uploadedFile->storeAs('temp', $newName, 'local');
        }

        // Read temp via the same disk used for storeAs (avoids "File does not exist" when public_path and disk root differ, e.g. symlinks).
        if (! Storage::disk('local')->exists($tempRelative)) {
            $msg = __('messages.pleaseSelectFile');
            throw new Exception($msg !== 'messages.pleaseSelectFile' ? $msg : 'Could not store uploaded file temporarily.');
        }

        $tempDiskPath = Storage::disk('local')->path($tempRelative);

        if (($width && $height) && File::extension($uploadedFile->getClientOriginalName()) !== 'svg') {
            Image::make($tempDiskPath)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save();
        }

        Storage::put($newPath, Storage::disk('local')->get($tempRelative), ['public']);

        Storage::disk('local')->delete($tempRelative);

        return $newName;
    }

    /**
     * Detect if a file with .xlsx extension is actually CSV (plain text) renamed to .xlsx.
     * XLSX files are ZIP archives and start with PK (0x50 0x4B).
     *
     * @param  string  $filePath  Full path to the file
     * @return bool True if file appears to be CSV disguised as xlsx
     */
    public static function isCsvDisguisedAsXlsx(string $filePath): bool
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return false;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            return false;
        }

        $fp = fopen($filePath, 'rb');
        if (! $fp) {
            return false;
        }

        $header = fread($fp, 4);
        fclose($fp);

        if (strlen($header) < 2) {
            return true; // Too short to be valid XLSX
        }

        // XLSX/ZIP magic: PK (0x50 0x4B)
        if ($header[0] === "\x50" && $header[1] === "\x4B") {
            return false; // Real XLSX
        }

        // Plain text / CSV - not a valid XLSX binary
        return true;
    }

    /**
     * @throws ApiException
     */
    public static function validateUploadedFile($uploadedFile)
    {
        // Check if file is valid
        if (! $uploadedFile->isValid()) {
            throw new ApiException('File was not uploaded correctly');
        }

        // Disallow dangerous extensions and mime types
        $forbiddenExtensions = [
            'php',
            'php3',
            'php4',
            'php5',
            'phtml',
            'phar',
            'sh',
            'htaccess',
            'pl',
            'cgi',
            'exe',
            'bat',
            'cmd',
            'com',
            'scr',
            'dll',
            'js',
            'jsp',
            'asp',
            'aspx',
            'cer',
            'csr',
            'jsp',
            'jspx',
            'war',
            'jar',
            'vb',
            'vbs',
            'wsf',
            'ps1',
            'ps2',
            'xml',
        ];

        $forbiddenMimeTypes = [
            'text/x-php',
            'application/x-php',
            'application/x-sh',
            'text/x-shellscript',
            'application/x-msdownload',
            'application/x-msdos-program',
            'application/x-executable',
            'application/x-csh',
            'application/x-bat',
            'application/x-msdos-windows',
            'application/x-javascript',
            'text/javascript',
            'application/javascript',
            'application/x-msdownload',
            'application/x-ms-installer',
            'application/x-dosexec',
            'application/x-cgi',
            'application/x-perl',
            'text/x-perl',
            'application/x-python',
            'text/x-python',
            'application/x-msdos-program',
            'application/x-msdos-windows',
            'application/x-msdos-batch',
            'application/x-msdos-cmd',
            'application/x-msdos-com',
            'application/x-msdos-scr',
            'application/x-msdos-dll',
            'application/x-msdos-js',
            'application/x-msdos-vbs',
            'application/x-msdos-ps1',
            'application/xml',
            'text/xml',
        ];

        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $mimeType = strtolower($uploadedFile->getMimeType());
        $originalName = strtolower($uploadedFile->getClientOriginalName());

        // Prevent double extensions (e.g. file.php.jpg)
        // Prevent double extensions (e.g. file.php.jpg)
        if (preg_match('/\.(php[0-9]?|phtml|phar|sh|pl|cgi|exe|bat|cmd|com|scr|dll|js|jsp|asp|aspx|cer|csr|jspx|war|jar|vb|vbs|wsf|ps1|ps2|xml)(\..+)?$/i', $originalName)) {
            throw new Exception('You are not allowed to upload files with dangerous extensions');
        }

        if (in_array($extension, $forbiddenExtensions)) {
            throw new Exception('You are not allowed to upload files with extension: '.$extension);
        }

        if (in_array($mimeType, $forbiddenMimeTypes)) {
            throw new Exception('You are not allowed to upload files with mime type: '.$mimeType);
        }

        // Prevent uploading .htaccess or similar files by name
        if (strpos($originalName, '.htaccess') !== false) {
            throw new Exception('You are not allowed to upload .htaccess files');
        }

        // Prevent uploading files with size less than 10 bytes
        if ($uploadedFile->getSize() <= 10) {
            throw new Exception('You are not allowed to upload a file with filesize less than 10 bytes');
        }

        // Prevent uploading files with null or empty extension
        if (empty($extension)) {
            throw new Exception('File must have a valid extension');
        }

        // Optionally, limit file name length
        if (strlen($uploadedFile->getClientOriginalName()) > 255) {
            throw new Exception('File name is too long');
        }

        // CRAVEVA
        if (company() && company()->package->max_storage_size > 0) {
            // Check if company has exceeded the storage limit
            $companyFilesSize = FileStorage::where('company_id', company()->id)->sum('size');
            $companyPackageMaxStorageSize = company()->package->max_storage_size;
            $companyPackageStorageUnit = company()->package->storage_unit;
            $maxStorageInBytes = $companyPackageMaxStorageSize * self::storageUnitToBytes($companyPackageStorageUnit);
            $companyAllowedStorageSize = $maxStorageInBytes - $companyFilesSize;

            if ($uploadedFile->getSize() > $companyAllowedStorageSize) {
                throw new Exception('You are not allowed to upload a file with filesize greater than '.$companyAllowedStorageSize.' bytes');
            }
        }
    }

    public static function storageUnitToBytes($unit, $size = 1)
    {
        $unit = strtolower($unit);
        $bytes = match ($unit) {
            'kb' => 1024,
            'mb' => 1024 * 1024,
            'gb' => 1024 * 1024 * 1024,
            'tb' => 1024 * 1024 * 1024 * 1024,
            'pb' => 1024 * 1024 * 1024 * 1024 * 1024,
            default => 1,
        };

        return $bytes * $size;
    }

    public static function generateNewFileName($currentFileName)
    {
        $ext = strtolower(File::extension($currentFileName));
        $newName = md5(microtime());

        return ($ext === '') ? $newName : $newName.'.'.$ext;
    }

    /**
     * @throws Exception
     */
    public static function uploadLocalOrS3($uploadedFile, $dir, $width = null, int $height = 400)
    {
        self::validateUploadedFile($uploadedFile);
        $dir = trim((string) $dir);
        $dir = trim($dir, '/\\');

        try {
            // If width and height is provided then upload image
            if (($width && $height)) {
                return self::uploadImage($uploadedFile, $dir, $width, $height);
            }

            // Add data to file_storage table
            $newName = self::fileStore($uploadedFile, $dir);

            $fileVisibility = [];

            if (config('filesystems.default') == 'local') {
                $fileVisibility = ['directory_visibility' => 'public', 'visibility' => 'public'];
            }

            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk(config('filesystems.default'));

            if (config('filesystems.default') === 'local') {
                self::createDirectoryIfNotExist($dir);
            }

            $filePath = ($dir !== '' ? ($dir.'/') : '').$newName;

            $realPath = $uploadedFile->getRealPath();
            if ($realPath === false || $realPath === '') {
                $content = $uploadedFile->get();
                if ($content === null || $content === '') {
                    $msg = __('messages.pleaseSelectFile');
                    if ($msg === 'messages.pleaseSelectFile') {
                        $msg = 'Please select a valid file to upload.';
                    }
                    throw new Exception($msg);
                }
                $disk->put($filePath, $content, $fileVisibility);
            } else {
                $disk->putFileAs($dir, $uploadedFile, $newName, $fileVisibility);
            }

            // Upload files to aws s3 or digitalocean or wasabi or minio
            if ($disk->missing($filePath)) {
                throw new Exception(__('app.fileNotUploaded').' File was not stored. Check disk: '.config('filesystems.default'));
            }

            return $newName;
        } catch (Exception $e) {
            throw new Exception(__('app.fileNotUploaded').' '.$e->getMessage().' on '.config('filesystems.default'));
        }
    }

    public static function fileStore($file, $folder, $generateNewName = '')
    {
        // Generate a new name if $generateNewName is empty
        $newName = $generateNewName ?: self::generateNewFileName($file->getClientOriginalName());

        // Retrieve enabled storage setting
        $setting = StorageSetting::where('status', 'enabled')->firstOrFail();
        $storageLocation = $setting->filesystem;

        // Store file information in the database
        $fileStorage = new FileStorage;
        $fileStorage->filename = $newName;
        $fileStorage->size = $file->getSize();
        $fileStorage->type = $file->getClientMimeType();
        $fileStorage->path = $folder;
        $fileStorage->storage_location = $storageLocation;
        $fileStorage->save();

        return $newName;
    }

    public static function deleteFile($filename, $folder)
    {
        if ($filename === null || $filename === '') {
            return true;
        }

        $dir = trim($folder, '/');

        // Check and delete file record from database
        if ($fileExist = FileStorage::where('filename', $filename)->first()) {
            $fileExist->delete();
        }

        $filePath = $dir.'/'.$filename;
        $disk = Storage::disk(config('filesystems.default'));

        // Delete from Cloud
        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            try {
                if ($disk->exists($filePath)) {
                    $disk->delete($filePath);
                }
            } catch (Exception $e) {
                return true;
            }

            return true;
        }

        // Delete from Local
        $path = public_path(Files::UPLOAD_FOLDER.'/'.$filePath);
        if (! File::exists($path)) {
            return true;
        }

        try {
            File::delete($path);
        } catch (\Throwable) {
            return true;
        }

        return true;
    }

    public static function deleteDirectory($folder)
    {
        $dir = trim($folder);
        try {
            Storage::deleteDirectory($dir);
        } catch (Exception $e) {
            return true;
        }

        return true;
    }

    public static function copy($from, $to)
    {
        Storage::disk(config('filesystems.default'))->copy($from, $to);
    }

    public static function createDirectoryIfNotExist($folder)
    {
        $folder = trim((string) $folder);
        $folder = trim($folder, '/\\');
        $relativePath = self::UPLOAD_FOLDER.($folder !== '' ? ('/'.$folder) : '');
        $directoryPath = public_path($relativePath);

        if (! File::exists($directoryPath)) {
            $created = File::makeDirectory($directoryPath, 0775, true);

            if (! $created && ! File::exists($directoryPath)) {
                throw new Exception('Unable to create a directory at '.$directoryPath.'.');
            }
        }

        if (File::exists($directoryPath) && ! is_writable($directoryPath)) {
            throw new Exception('Directory is not writable at '.$directoryPath.'.');
        }
    }

    public static function uploadImage($uploadedFile, string $folder, $width = null, int $height = 800)
    {
        $folder = trim($folder, '/\\');
        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());

        $tempPath = public_path(self::UPLOAD_FOLDER.'/temp/'.$newName);

        /** Check if folder exits or not. If not then create the folder */
        self::createDirectoryIfNotExist($folder);
        self::createDirectoryIfNotExist('temp');

        $newPath = ($folder !== '' ? ($folder.'/') : '').$newName;
        $tempRelative = 'temp/'.$newName;

        $realPath = $uploadedFile->getRealPath();
        if ($realPath === false || $realPath === '') {
            $content = $uploadedFile->get();
            if ($content === null || $content === '') {
                $msg = __('messages.pleaseSelectFile');
                if ($msg === 'messages.pleaseSelectFile') {
                    $msg = 'Please select a valid file to upload.';
                }
                throw new Exception($msg);
            }
            File::put($tempPath, $content);
        } else {
            $uploadedFile->storeAs('temp', $newName, 'local');
        }

        if (! Storage::disk('local')->exists($tempRelative)) {
            $msg = __('messages.pleaseSelectFile');
            throw new Exception($msg !== 'messages.pleaseSelectFile' ? $msg : 'Could not store uploaded file temporarily.');
        }

        $tempDiskPath = Storage::disk('local')->path($tempRelative);

        // Resizing image if width and height is provided
        $svgNot = File::extension($uploadedFile->getClientOriginalName()) !== 'svg';
        $webPNot = File::extension($uploadedFile->getClientOriginalName()) !== 'webp';

        if ($width && $height && $svgNot && $webPNot) {
            Image::make($tempDiskPath)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save();
        }

        Storage::disk(config('filesystems.default'))->put($newPath, Storage::disk('local')->get($tempRelative));
        self::fileStore($uploadedFile, $folder, $newName);

        Storage::disk('local')->delete($tempRelative);

        return $newName;
    }

    public static function uploadLocalFile($fileName, $path, $companyId = null): void
    {
        if (! File::exists(public_path(Files::UPLOAD_FOLDER.'/'.$path.'/'.$fileName))) {
            return;
        }

        self::saveFileInfo($fileName, $path, $companyId);
        self::storeLocalFileOnCloud($fileName, $path);
    }

    public static function saveFileInfo($fileName, $path, $companyId = null)
    {
        $filePath = public_path(Files::UPLOAD_FOLDER.'/'.$path.'/'.$fileName);

        $fileStorage = FileStorage::where('filename', $fileName)->first() ?: new FileStorage;
        $fileStorage->company_id = $companyId;
        $fileStorage->filename = $fileName;
        $fileStorage->size = File::size($filePath);
        $fileStorage->type = File::mimeType($filePath);
        $fileStorage->path = $path;
        $fileStorage->storage_location = config('filesystems.default');
        $fileStorage->save();
    }

    public static function storeLocalFileOnCloud($fileName, $path)
    {
        if (config('filesystems.default') != 'local') {
            $filePath = public_path(Files::UPLOAD_FOLDER.'/'.$path.'/'.$fileName);
            try {
                $contents = File::get($filePath);
                Storage::disk(config('filesystems.default'))->put($path.'/'.$fileName, $contents);

                // TODO: Delete local file in Next release
                // File::delete($filePath);
                return true;
            } catch (Exception $e) {
                info($e->getMessage());
            }
        }

        return false;
    }

    /**
     * fixLocalUploadFiles is used to fix the local upload files
     *
     * Example of $model
     * $model = Company::class;
     *
     * Example of $columns
     * $columns = [
     *     [
     *        'name' => 'logo',
     *       'path' => 'company'
     *    ]
     * ];
     *
     * @param  mixed  $model
     * @return void
     */
    public static function fixLocalUploadFiles($model, array $columns)
    {
        foreach ($columns as $column) {
            $name = $column['name'];
            $path = $column['path'];

            $filesData = $model::withoutGlobalScopes()->whereNotNull($name)->get();

            foreach ($filesData as $item) {
                /** @phpstan-ignore-next-line */
                $fileName = $item->{$name};
                /** @phpstan-ignore-next-line */
                $companyId = ($model == Company::class) ? $item->id : $item->company_id;

                $filePath = public_path(self::UPLOAD_FOLDER.'/'.$path.'/'.$fileName);

                if (! File::exists($filePath)) {
                    continue;
                }

                self::saveFileInfo($fileName, $path, $companyId);
                self::storeLocalFileOnCloud($fileName, $path);
            }
        }
    }

    public static function getFormattedSizeAndStatus($maxSizeKey)
    {
        try {
            // Retrieve the raw value from php.ini
            $maxSize = ini_get($maxSizeKey);

            // Convert the size to bytes
            $sizeInBytes = self::returnBytes($maxSize);

            // Format the size in either MB or GB
            if ($sizeInBytes >= 1 << 30) {
                return [
                    'size' => round($sizeInBytes / (1 << 30), 2).' GB',
                    'greater' => true,
                ];
            }

            $mb = $sizeInBytes / 1048576;

            if ($sizeInBytes >= 1 << 20) {
                return [
                    'size' => round($sizeInBytes / (1 << 20), 2).' MB',
                    'greater' => $mb >= self::REQUIRED_FILE_UPLOAD_SIZE,
                ];
            }

            if ($sizeInBytes >= 1 << 10) {
                return [
                    'size' => round($sizeInBytes / (1 << 10), 2).' KB',
                    'greater' => false,
                ];
            }

            return [
                'size' => $sizeInBytes.' Bytes',
                'greater' => false,
            ];
        } catch (Exception $e) {
            return [
                'size' => '0 Bytes',
                'greater' => true,
            ];
        }
    }

    public static function getUploadMaxFilesize()
    {
        return self::getFormattedSizeAndStatus('upload_max_filesize');
    }

    public static function getPostMaxSize()
    {
        return self::getFormattedSizeAndStatus('post_max_size');
    }

    // Helper function to convert human-readable size to bytes
    public static function returnBytes($val)
    {
        $val = trim((string) $val);
        if ($val === '') {
            return 0;
        }

        $last = strtolower($val[strlen($val) - 1]);

        if (! ctype_alpha($last)) {
            return (int) $val;
        }

        $valNew = (float) substr($val, 0, -1);

        switch ($last) {
            case 'g':
                $valNew *= 1024;
            case 'm':
                $valNew *= 1024;
            case 'k':
                $valNew *= 1024;
        }

        return $valNew;
    }
}
