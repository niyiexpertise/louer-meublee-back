<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\FileStockage;
use Illuminate\Support\Facades\Schema;



class FileService
{
    protected $serverUrl;
    protected $disk;

    public function __construct()
    {
        if (Schema::hasTable('settings')) {
            $setting = Setting::first();
        }
        if (Schema::hasTable('settings')) {
            $s3Config = FileStockage::where('type', 's3')->where('is_actif', 1)->first();

                }

        $this->serverUrl = $setting->adresse_serveur_fichier ?? url('/');
        $this->disk = $s3Config ->type ?? 'defaut';
    }


    public function uploadFiles($files, string $directory, $type)
    {
        if (is_array($files)) {
            return $this->uploadMultipleFiles($files, $directory, $type);
        }

        return $this->uploadSingleFile($files, $directory, $type);
    }

    private function uploadMultipleFiles(array $files, string $directory, $type): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file->isValid()) {
                $paths[] = $this->uploadSingleFile($file, $directory, $type);
            }
        }
        return $paths;
    }

    private function uploadSingleFile($file, string $directory, $type)
    {
        $allowedExtensions = $this->getAllowedExtensions($type);
        $extension = $file->getClientOriginalExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return $this->generateErrorResponse($allowedExtensions, $extension);
        }

        $filename = uniqid() . '.' . $extension;
        $filePath = $directory . '/' . $filename;

        if ($this->disk === 's3') {
            // Code spécifique pour S3
            if (in_array($extension, $this->getAllowedExtensions('extensionImage'))) {
                // Compression et sauvegarde de l'image pour S3
                $compressedImage = $this->compressImage($file);
                Storage::disk($this->disk)->put($filePath, $compressedImage);
            } else {
                // Stockage des autres fichiers sur S3
                Storage::disk($this->disk)->putFileAs($directory, $file, $filename);
            }
        } else {
            // Code de stockage local
            $destinationPath = public_path($directory);

            if (in_array($extension, $this->getAllowedExtensions('extensionImage'))) {
                $this->compressAndSaveImage($file, $destinationPath, $filename);
            } else {
                $file->move($destinationPath, $filename);
            }
        }

        $chemin = '/' . $directory . '/' . $filename;

        return [
            'fails' => false,
            'result' => $chemin
        ];
    }

    private function getAllowedExtensions($type): array
    {
        $extensions = [
            'extensionImage' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif'],
            'extensionImageVideo' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'mp4', 'mov', 'avi', 'mkv', 'mpeg', 'webm', 'jfif'],
            'extensionDocument' => ['pdf'],
            'extensionDocumentImage' => ['pdf','jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif']
        ];

        return $extensions[$type] ?? [];
    }

    private function generateErrorResponse(array $allowedExtensions, string $extension): array
    {
        $allowedExtensionsString = implode(', ', $allowedExtensions);
        return [
            'fails' => true,
            'result' => "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensionsString. Le fichier fourni a l'extension : $extension."
        ];
    }

    private function compressAndSaveImage($file, string $destinationPath, string $filename)
    {
        $image = Image::make($file->getRealPath());
        $compressionQuality = $this->calculateCompressionQuality($image, 1024 * 1024);
        $image->save($destinationPath . '/' . $filename, $compressionQuality);
    }

    private function compressImage($file)
    {
        $image = Image::make($file->getRealPath());
        $compressionQuality = $this->calculateCompressionQuality($image, 1024 * 1024);
        return $image->encode('jpg', $compressionQuality)->__toString();
    }

    private function calculateCompressionQuality($image, $targetSizeBytes)
    {
        $minQuality = 0;
        $maxQuality = 100;
        $quality = 85; // Start with a reasonable default
        $attempts = 10;

        while ($attempts > 0) {
            $imageEncoded = $image->encode('jpg', $quality);
            $encodedSize = strlen($imageEncoded);

            if ($encodedSize > $targetSizeBytes) {
                $maxQuality = $quality;
            } else {
                $minQuality = $quality;
            }

            $quality = ($minQuality + $maxQuality) / 2;
            $attempts--;
        }

        return (int) $quality;
    }
}
