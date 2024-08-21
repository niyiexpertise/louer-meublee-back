<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
class FileService
{
    protected $serverUrl;

    public function __construct()
    {
        $this->serverUrl = Setting::first()->adresse_serveur_fichier ?? url('/');
    }

    public function uploadFiles($files, string $directory)
    {
        if (is_array($files)) {
            return $this->uploadMultipleFiles($files, $directory);
        }

        return $this->uploadSingleFile($files, $directory);
    }

 
    private function uploadMultipleFiles(array $files, string $directory): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file->isValid()) {
                $paths[] = $this->uploadSingleFile($file, $directory);
            }
        }

        return $paths;
    }

    private function uploadSingleFile($file, string $directory): string
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path($directory), $filename);
        
        $chemin='/'.$directory.'/'.$filename;
        return  $chemin;
    }

       

}





