<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class FileStorageCustomConfigProvider extends ServiceProvider
{
    public function register()
    {
        if (App::environment('demo')) {
            return true;
        }

        try {
            $setting = DB::table('file_storage_settings')->where('status', 'enabled')->first();

            if ($setting === null) {
                return;
            }

            switch ($setting->filesystem) {

                case 'aws_s3':
                    $authKeys = json_decode(Crypt::decryptString($setting->auth_keys));
                    $driver = $authKeys->driver;
                    $key = $authKeys->key;
                    $secret = $authKeys->secret;
                    $region = $authKeys->region;
                    $bucket = $authKeys->bucket;
                    Config::set('filesystems.default', $driver);
                    Config::set('filesystems.cloud', $driver);
                    Config::set('filesystems.disks.s3.key', $key);
                    Config::set('filesystems.disks.s3.secret', $secret);
                    Config::set('filesystems.disks.s3.region', $region);
                    Config::set('filesystems.disks.s3.bucket', $bucket);
                    break;

                case 'digitalocean':
                    $authKeys = json_decode(Crypt::decryptString($setting->auth_keys));
                    $driver = $authKeys->driver;
                    $key = $authKeys->key;
                    $secret = $authKeys->secret;
                    $region = $authKeys->region;
                    $bucket = $authKeys->bucket;
                    Config::set('filesystems.default', 'digitalocean');
                    Config::set('filesystems.cloud', 'digitalocean');
                    Config::set('filesystems.disks.digitalocean.key', $key);
                    Config::set('filesystems.disks.digitalocean.secret', $secret);
                    Config::set('filesystems.disks.digitalocean.region', $region);
                    Config::set('filesystems.disks.digitalocean.bucket', $bucket);
                    Config::set('filesystems.disks.digitalocean.endpoint', 'https://' . $region . '.digitaloceanspaces.com');
                    break;

                case 'wasabi':
                    $authKeys = json_decode(Crypt::decryptString($setting->auth_keys));
                    $driver = $authKeys->driver;
                    $key = $authKeys->key;
                    $secret = $authKeys->secret;
                    $region = $authKeys->region;
                    $bucket = $authKeys->bucket;
                    Config::set('filesystems.default', 'wasabi');
                    Config::set('filesystems.cloud', 'wasabi');
                    Config::set('filesystems.disks.wasabi.key', $key);
                    Config::set('filesystems.disks.wasabi.secret', $secret);
                    Config::set('filesystems.disks.wasabi.region', $region);
                    Config::set('filesystems.disks.wasabi.bucket', $bucket);
                    Config::set('filesystems.disks.wasabi.endpoint', 'https://s3.' . $region . '.wasabisys.com');
                    break;
                case 'minio':
                    $authKeys = json_decode(Crypt::decryptString($setting->auth_keys));
                    $driver = $authKeys->driver;
                    $key = $authKeys->key;
                    $secret = $authKeys->secret;
                    $region = $authKeys->region;
                    $bucket = $authKeys->bucket;
                    $endpoint = $authKeys->endpoint;
                    Config::set('filesystems.default', 'minio');
                    Config::set('filesystems.cloud', 'minio');
                    Config::set('filesystems.disks.minio.key', $key);
                    Config::set('filesystems.disks.minio.secret', $secret);
                    Config::set('filesystems.disks.minio.region', $region);
                    Config::set('filesystems.disks.minio.bucket', $bucket);
                    Config::set('filesystems.disks.minio.endpoint', $endpoint);
                    break;

                // For local storage
                default:
                    Config::set('filesystems.default', $setting->filesystem);
                    break;
            }
        }
        // @codingStandardsIgnoreLine
        catch (\Throwable $e) {
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //

    }
}
