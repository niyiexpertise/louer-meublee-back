<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FileStockage;

class DynamicFilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $s3Config = FileStockage::where('type', 's3')->where('is_actif', 1)->first();

        if ($s3Config) {
            config([
                'filesystems.default' => 's3',
                'filesystems.disks.s3' => [
                    'driver' => 's3',
                    'key' => $s3Config->access_key_id,
                    'secret' => $s3Config->secret_access_key,
                    'region' => $s3Config->default_region,
                    'bucket' => $s3Config->bucket,
                    'url' => $s3Config->url,
                    
                ],
            ]);
        }
    }
}
