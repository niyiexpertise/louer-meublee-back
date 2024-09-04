<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;

class FileService
{
    protected $serverUrl;

    public function __construct()
    {
        $this->serverUrl = Setting::first()->adresse_serveur_fichier ?? url('/');
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
        $destinationPath = public_path($directory);

        if (in_array($extension, $this->getAllowedExtensions('extensionImage'))) {
            $this->compressAndSaveImage($file, $destinationPath, $filename);
        } else {
            $file->move($destinationPath, $filename);
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
            'extensionDocument' => ['pdf']
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
