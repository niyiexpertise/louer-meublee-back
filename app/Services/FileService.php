<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Spatie\ImageOptimizer\OptimizerChainFactory;
class FileService
{
    protected $serverUrl;

    public function __construct()
    {
        $this->serverUrl = Setting::first()->adresse_serveur_fichier ?? url('/');
    }

    public function uploadFiles($files, string $directory,$type)
    {
        if (is_array($files)) {
            return $this->uploadMultipleFiles($files, $directory,$type);
        }

        return $this->uploadSingleFile($files, $directory,$type);
    }


    private function uploadMultipleFiles(array $files, string $directory,$type): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file->isValid()) {
                $paths[] = $this->uploadSingleFile($file, $directory,$type);
            }
        }

        return $paths;
    }

    private function uploadSingleFile($file, string $directory,$type)
    {

        $extensionImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif'];

        $extensionImageVideo = [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 
            'mp4', 'mov', 'avi', 'mkv', 'mpeg', 'webm', 'jfif'
        ];

        $extensionDocument = [
            'pdf'
        ];

        $extension = $file->getClientOriginalExtension();

        if($type == 'extensionImage'){
            if (!in_array($extension, $extensionImage)) {
                $allowedExtensions = implode(', ', $extensionImage);
                return [
                    'fails' => true,
                    'result' =>  "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensions. Le fichier fourni a l'extension : $extension."
                ];
            }
        }else if($type == 'extensionImageVideo'){
            if (!in_array($extension, $extensionImageVideo)) {
                $allowedExtensions = implode(', ', $extensionImageVideo);
                return [
                    'fails' => true,
                    'result' => "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensions. Le fichier fourni a l'extension : $extension."
                ];
            }
        }else if($type == 'extensionDocument'){
            if (!in_array($extension, $extensionDocument)) {
                $allowedExtensions = implode(', ', $extensionDocument);
                return [
                    'fails' => true,
                    'result' => "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensions. Le fichier fourni a l'extension : $extension."
                ];
            }
        }else{
            return [
                'fails' => true,
                'result' => 'Entrez une extension valide'
            ];
        }

        $request = new Request();

        if($type == 'extensionImage'){
            if (in_array($extension, $extensionImage)) {
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                // dd($file);

                $path = $file->move(public_path($directory), $filename);
                // $filePath = $file->store($directory);
                $optimizerChain = OptimizerChainFactory::create();
                // $optimizerChain->optimize('C:\Users\ayena\louer-meublee-back\public\image\testImage\66d84d2d702a3.jpg');
                $chemin='/'.$directory.'/'.$filename;
                
                $result = "no";

                if($optimizerChain->optimize('C:\Users\ayena\louer-meublee-back\public\image\testImage\66d86a5756d43.jpg')){
                    $result = "yes";
                    return $result;
                }

            }
        }


        // $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        // $path = $file->move(public_path($directory), $filename);
        
        // $chemin='/'.$directory.'/'.$filename;

        return [
            'fails' => false,
            'result' => [
                "path" => $path->getPathname(),
                "chemin" => $chemin,
                "result" => $result
            ],
            // 'result' => $chemin
        ];
    }



}





