<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
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
        // Définir des valeurs par défaut pour S3
        $defaultS3Config = [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID', ''),
            'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET', ''),
            'url' => env('AWS_URL', ''),
        ];

        // Vérifiez si la table 'file_stockages' existe
        if (Schema::hasTable('file_stockages')) {
            $s3Config = FileStockage::where('type', 's3')->where('is_actif', 1)->first();

            if ($s3Config) {
                $defaultS3Config = [
                    'driver' => 's3',
                    'key' => $s3Config->access_key_id,
                    'secret' => $s3Config->secret_access_key,
                    'region' => $s3Config->default_region,
                    'bucket' => $s3Config->bucket,
                    'url' => $s3Config->url,
                ];
            }
        }

        // Configurer le système de fichiers avec les valeurs par défaut ou dynamiques
        config([
            'filesystems.default' => 's3',
            'filesystems.disks.s3' => $defaultS3Config,
        ]);
    }
}
