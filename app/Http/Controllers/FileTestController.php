<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Mostafaznv\PdfOptimizer\Laravel\Facade\PdfOptimizer;
use Mostafaznv\PdfOptimizer\Enums\ColorConversionStrategy;
use Mostafaznv\PdfOptimizer\Enums\PdfSettings;


class FileTestController extends Controller
{
    protected $fileService;
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
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
    $extensionImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif'];
    $extensionImageVideo = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff',
        'mp4', 'mov', 'avi', 'mkv', 'mpeg', 'webm', 'jfif'
    ];
    $extensionDocument = ['pdf'];

    $extension = strtolower($file->getClientOriginalExtension());

    if ($type == 'extensionImage') {
        if (!in_array($extension, $extensionImage)) {
            $allowedExtensions = implode(', ', $extensionImage);
            return [
                'fails' => true,
                'result' => "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensions. Le fichier fourni a l'extension : $extension."
            ];
        }
    } elseif ($type == 'extensionImageVideo') {
        if (!in_array($extension, $extensionImageVideo)) {
            $allowedExtensions = implode(', ', $extensionImageVideo);
            return [
                'fails' => true,
                'result' => "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensions. Le fichier fourni a l'extension : $extension."
            ];
        }
    } elseif ($type == 'extensionDocument') {
        if (!in_array($extension, $extensionDocument)) {
            $allowedExtensions = implode(', ', $extensionDocument);
            return [
                'fails' => true,
                'result' => "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensions. Le fichier fourni a l'extension : $extension."
            ];
        }
    } else {
        return [
            'fails' => true,
            'result' => 'Entrez une extension valide'
        ];
    }

    // Générer un nom de fichier unique et déplacer le fichier
    $filename = uniqid() . '.' . $extension;
    $path = $file->move(public_path($directory), $filename);
    $chemin = '/' . $directory . '/' . $filename;

    // dd( $path);

    // Vérifier la taille du fichier et compresser si nécessaire
    if (filesize($path) > 3 * 1024 * 1024) { // Taille supérieure à 3 Mo
        $compressedPath = $this->compressFile($path, $extension, $directory);
        if ($compressedPath) {
            $chemin = $compressedPath;
        }
    }

    return [
        'fails' => false,
        'result' => $chemin
    ];
}

private function compressFile($path, $extension, $directory)
{
    $compressedFilename = uniqid() . '.' . $extension;
    $compressedPath = public_path($directory) . '/' . $compressedFilename;

    $extensionImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif'];
    $extensionImageVideo = ['mp4', 'mov', 'avi', 'mkv', 'mpeg', 'webm'];
    $extensionDocument = ['pdf'];

    if (in_array($extension, $extensionImage)) {
        $this->compressAndResizeImage($path, $compressedPath, null, null, 70);
    } elseif (in_array($extension, $extensionImageVideo)) {
        $this->compressAndResizeVideo($path, $compressedPath, null, null, 1000);
    } elseif (in_array($extension, $extensionDocument)) {
        $this->compressPdf($path, $compressedPath);
    } else {
        return null;
    }

    // return $path;

    return '/' . $directory . '/' . $compressedFilename;
}

private function compressAndResizeImage($from, $to, $mw, $mh, $quality)
{
    $extFrom = strtolower(pathinfo($from, PATHINFO_EXTENSION));
    $extTo = strtolower(pathinfo($to, PATHINFO_EXTENSION));

    // Ouvrir l'image source
    switch ($extFrom) {
        case 'jpg':
        case 'jpeg':
            $fn = 'imagecreatefromjpeg';
            break;
        case 'png':
            $fn = 'imagecreatefrompng';
            break;
        case 'gif':
            $fn = 'imagecreatefromgif';
            break;
        case 'webp':
            $fn = 'imagecreatefromwebp';
            break;
        case 'bmp':
            $fn = 'imagecreatefrombmp';
            break;
        default:
            exit("$from - Format de fichier non pris en charge");
    }

    // Ouvrir l'image source
    if (!function_exists($fn)) {
        exit("La fonction $fn n'est pas disponible.");
    }
    $img = $fn($from);

    // Redimensionner l'image si besoin
    if ($mw != null || $mh != null) {
        $sw = imagesx($img);
        $sh = imagesy($img);

        $rw = ($mw != null && $sw > $mw) ? $mw / $sw : 1;
        $rh = ($mh != null && $sh > $mh) ? $mh / $sh : 1;

        $rr = min($rw, $rh);
        $img = imagescale($img, floor($rr * $sw), floor($rr * $sh));
    }

    // Sauvegarder et compresser l'image
    if ($extTo == "gif") {
        imagegif($img, $to);
    } else {
        if ($extTo == "jpg") {
            $quality = ($quality == null || !is_numeric($quality) || $quality < 0 || $quality > 100) ? 70 : $quality;
        } elseif ($extTo == "webp") {
            $quality = ($quality == null || !is_numeric($quality) || $quality < -1 || $quality > 100) ? -1 : $quality;
        } elseif ($extTo == "png") {
            $quality = ($quality == null || !is_numeric($quality) || $quality < -1 || $quality > 9) ? -1 : $quality;
        }
        $fn = "image" . ($extTo == "jpg" ? "jpeg" : $extTo);
        $fn($img, $to, $quality);
    }
}

private function compressAndResizeVideo($from, $to, $mw, $mh, $quality)
{
    // Utilisation de FFmpeg pour compresser et redimensionner les vidéos
    $command = "ffmpeg -i $from";

    if ($mw != null || $mh != null) {
        $command .= " -vf scale=" . ($mw ?: -1) . ":" . ($mh ?: -1);
    }

    if ($quality != null) {
        $command .= " -b:v " . escapeshellarg($quality . "k");
    }

    $command .= " $to";
    shell_exec($command);
}

private function compressPdf($from, $to)
{
   
}


    public function ajoutFile(Request $request){

        // return "floatval"("1");
        // imagecreatefrompng()
        $request->validate([
            'icone' => 'required'
        ]);
        // dd($request->icone);
        $identity_profil_url =$this->fileService->uploadFiles($request->file('icone'), 'image/testImage', 'extensionImage');
        if ($identity_profil_url['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
        }

        return (new ServiceController())->apiResponse(200, [], $identity_profil_url['result']);
    }


//     public function upload(Request $request)
// {
//     $file = $request->file('image');
//     $filePath = $file->store('images');

//     $optimizerChain = OptimizerChainFactory::create();
//     $optimizerChain->optimize(storage_path('app/' . $filePath));

//     return response()->json(['message' => 'Image uploaded and optimized successfully!']);
// }

}
