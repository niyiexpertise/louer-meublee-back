<?php

namespace App\Http\Controllers;

use App\Models\verification_document;
use App\Models\verification_statut;
use App\Models\User;
use App\Models\Document;
use App\Models\Commission;
use App\Models\Notification;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class VerificationDocumentController extends Controller
{

/**
 * @OA\Get(
 *     path="/api/verificationdocument/index",
 *     summary="Récupérer la liste des documents de vérification par utilisateur",
 *     description="Récupère la liste des documents de vérification groupés par utilisateur avec leur statut.",
 *     operationId="listVerificationDocumentsByUser",
 *     tags={"verification_document"},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des documents de vérification par utilisateur",
 *         @OA\JsonContent(
 *             ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Une erreur est survenue lors de la récupération des documents de vérification."),
 *         ),
 *     ),
 * )
 */
public function index()
{
    try {
        $users = User::where('is_hote', 0)
                     ->whereHas('verificationDocuments', function ($query) {
                         $query->whereHas('verificationStatut', function ($query) {
                             $query->where('status', 0);
                         });
                     })
                     ->with('verificationDocuments', 'verificationDocuments.verificationStatut', 'verificationDocuments.document')
                     ->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'Aucun utilisateur avec des documents de vérification en attente trouvé.'], 404);
                    }

        $verificationDocumentsByUser = [];

        foreach ($users as $user) {
            $verificationDocuments = $user->verificationDocuments->map(function ($verificationDocument) {
                return [
                    'id_verification_document' => $verificationDocument->id,
                    'document_id' => $verificationDocument->document_id,
                    'document_name' => $verificationDocument->document ? $verificationDocument->document->name : null,
                    'path' => $verificationDocument->path,
                    'status' => $verificationDocument->verificationStatut ? $verificationDocument->verificationStatut->status : null,
                ];
            });

            $userInfo = [
                'id_user' => $user->id,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'email' => $user->email,
                'code_pays' => $user->code_pays,
                'telephone' => $user->telephone,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
                'postal_code' => $user->postal_code,
                'sexe' => $user->sexe,
                'is_hote' => $user->is_hote,
                'verification_documents' => $verificationDocuments,
            ];

            $verificationDocumentsByUser[] = $userInfo;
        }

        return response()->json(['data' => $verificationDocumentsByUser], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Une erreur est survenue lors de la récupération des documents de vérification.'], 500);
    }

}




/**
 * @OA\Post(
 *     path="/api/verificationdocument/store",
 *     summary="Enregistrer des documents de vérification avec des images",
 *     tags={"verification_document"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données pour enregistrer les documents de vérification",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="id_document[]",
 *                     description="ID du document",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                 ),
 *                 @OA\Property(
 *                     property="image_piece[]",
 *                     description="Image de la pièce",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary"),
 *                 ),
 *                 required={"id_document", "image_piece"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Documents de vérification créés avec succès"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation"
 *     )
 * )
 */
public function store(Request $request)
{
    try {
        $verificationDocuments = [];
        $user_id = auth()->user()->id;
        $idDocuments =$request->id_document;
        $imagePieces = $request->file('image_piece');     
        if (count($idDocuments) !== count($imagePieces)) {
            return response()->json(['error' => 'Les tableaux id_document et image_piece doivent avoir la même longueur.'], 400);
        }

        foreach ($idDocuments as $key => $idDocument) {
            $imagePiece = $imagePieces[$key];

            $path_name = uniqid() . '.' . $imagePiece->getClientOriginalExtension();
            
            $base_url = url('/');
            $path_url = $base_url . '/image/document_verification/' . $path_name;
            
            $verificationDocument = new verification_document();
            $verificationDocument->user_id = $user_id; 

            $verificationDocument->document_id = $idDocument;
            $verificationDocument->path = $path_url;
            $verificationDocument->save();
            
            $verificationStatut = new verification_statut();
            $verificationStatut->verification_document_id = $verificationDocument->id;
            $verificationStatut->save();
            $path_path = $imagePiece->move(public_path('image/document_verification'), $path_name);
            $verificationDocuments[] = $verificationDocument;

            $adminUsers = User::where('is_admin', 1)->get();
            foreach ($adminUsers as $adminUser) {
                $notification = new Notification();
                $notification->user_id = $adminUser->id;
                $notification->name = "Une demande d'être hôte vient d'être envoyée.";
                $notification->save();
            }
        }

        return response()->json(['data' => 'Verification documents crees avec succes.', 'verification_documents' => $verificationDocuments], 201);
    } catch (Exception $e) {
        return response()->json($e);
    }
}

 
    /**
     * Display the specified resource.
     */

         /**
     * Display the specified resource.
     */

     /**
   * @OA\Get(
   *     path="/api/verificationdocument/show/{id}",
   *     summary="Afficher les detail d'une demande pour être hôte",
   *     tags={"verification_document"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID of the user",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Information detaillée e la demande pour être hote"
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="verification_document not found"
   *     )
   * )
   */
    public function show($userId)
    {
        
        try {
            
            $user = User::where('id', $userId)
                        ->where('is_hote', 0)
                        ->whereHas('verificationDocuments')
                        ->with('verificationDocuments', 'verificationDocuments.verificationStatut', 'verificationDocuments.document')
                        ->firstOrFail();
            $verificationDocuments = $user->verificationDocuments->map(function ($verificationDocument) {
                return [
                    'id_verification_document' => $verificationDocument->id,
                    'document_id' => $verificationDocument->document_id,
                    'document_name' => $verificationDocument->document ? $verificationDocument->document->name : null,
                    'path' => $verificationDocument->path,
                    'status' => $verificationDocument->verificationStatut ? $verificationDocument->verificationStatut->status : null,
                ];
            });
    
            $userInfo = [
                'id_user' => $user->id,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'email' => $user->email,
                'code_pays' => $user->code_pays,
                'telephone' => $user->telephone,
                'country' => $user->country,
                'city' => $user->city,
                'address' => $user->address,
                'postal_code' => $user->postal_code,
                'sexe' => $user->sexe,
                'photo_profile'=> $user->file_profil,
                'is_hote' => $user->is_hote,
                'verification_documents' => $verificationDocuments,
            ];
    
            return response()->json(['data' => $userInfo], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la récupération des détails de l\'utilisateur.'], 500);
        }
    }

 

    /**
     * Valider les documents en un coup.
     *
     * Valide les documents de vérification pour un utilisateur et change son statut en tant qu'hôte.
     * 
     * @OA\Post(
     *     path="/api/verificationdocument/hote/valider/all",
     *     summary="Valider les documents en un coup ,bref valider tout en un clic",
     *     description="Valide les documents de vérification pour un utilisateur et change son statut en tant qu'hôte.",
     *     tags={"Validation documents pour etre hôte"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données requises pour la validation des documents",
     *         @OA\JsonContent(
     *             required={"user_id", "verification_document_ids"},
     *             @OA\Property(property="user_id", type="integer", description="L'identifiant de l'utilisateur"),
     *             @OA\Property(property="verification_document_ids", type="array", description="Les identifiants des documents de vérification à valider",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès. Les documents ont été validés avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Message de succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation. Vérifiez les données d'entrée.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur. Veuillez réessayer ultérieurement.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     )
     * )
     */ 
public function validateDocuments(Request $request)
{
    $data = $request->validate([
        'user_id' => 'required|integer',
        'verification_document_ids' => 'required|array',
    ]);

    $user_id = $data['user_id'];
    $verification_document_ids = $data['verification_document_ids'];
    $verificationDocumentsExist = verification_statut::whereIn('verification_document_id', $verification_document_ids)->exists();
    if (!$verificationDocumentsExist) {
        return response()->json(['error' => 'IDs de documents de vérification invalides.'], 400);
    }

    try {
        foreach ($verification_document_ids as $verification_document_id) {
            $verificationStatut = verification_statut::where('verification_document_id', $verification_document_id)->first();
            if ($verificationStatut) {
                $verificationStatut->update(['status' => 1]);
            }
        }

        $user = User::findOrFail($user_id);
        $user->update(['is_hote' => 1]);
        $notification = new Notification();
        $notification->user_id = $user_id;
        $notification->name = "Votre demande d'être hôte a été validée avec succès.";
        $notification->save();
        $commission=new Commission();
        $commission->user_id=$user->id;
        $commission->valeur=5;
        $commission->save();

        return response()->json(['message' => 'Documents validés avec succès et notification envoyée.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Une erreur est survenue lors de la validation des documents.'], 500);
    }
}


/**
 * @OA\Post(
 *     path="/api/verificationdocument/hote/valider/one",
 *     summary="Valider un document de vérification pour devenir hôte(ici on valide document par document)",
 *     tags={"Validation documents pour etre hôte"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données pour valider un document de vérification",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="user_id",
 *                     description="ID de l'utilisateur",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="verification_document_id",
 *                     description="ID du document de vérification à valider",
 *                     type="integer"
 *                 ),
 *                 required={"user_id", "verification_document_id"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document de vérification validé avec succès"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation des données"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Document de vérification non trouvé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur"
 *     )
 * )
 */

public function validateDocument(Request $request)
{
    $data = $request->validate([
        'user_id' => 'required|integer',
        'verification_document_id' => 'required|integer',
    ]);

    $user_id = $data['user_id'];
    $verification_document_id = $data['verification_document_id'];

    try {
        $verificationStatut = verification_statut::where('verification_document_id', $verification_document_id)->first();
        if (!$verificationStatut) {
            return response()->json(['error' => 'Le document de vérification spécifié n\'existe pas.'], 404);
        }

        $verificationStatut->update(['status' => 1]);

        $user = User::findOrFail($user_id);
        $allDocumentsValidated = $user->verificationDocuments()->whereHas('verificationStatut', function ($query) {
            $query->where('status', 0);
        })->count() === 0;

        if ($allDocumentsValidated) {
            $user->update(['is_hote' => 1]);
            $notification = new Notification();
            $notification->user_id = $user_id;
            $notification->name = "Votre demande d'être hôte a été validée avec succès.";
            $notification->save();
            $commission=new Commission();
            $commission->user_id=$user->id;
            $commission->valeur=5;
            $commission->save();

        }

        return response()->json(['message' => 'Document validé avec succès.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Une erreur est survenue lors de la validation du document.'], 500);
    }
}

/**
 * @OA\Get(
 *     path="/api/users/result/demande",
 *     summary="Affiche pour un utiliateur connecté,le statut de ses documents soumis et le statut de sa demande",
 *     tags={"verification_document"},
 *     @OA\Response(
 *         response=200,
 *         description="Demandes de vérification récupérées avec succès pour l'utilisateur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="verification_documents",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="document_id", type="integer"),
 *                     @OA\Property(property="document_name", type="string"),
 *                     @OA\Property(property="path", type="string"),
 *                     @OA\Property(property="status", type="integer")
 *                 )
 *             ),
 *             @OA\Property(property="request_status", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la récupération des documents de vérification"
 *     )
 * )
 */


public function userVerificationRequests()
{
    try {
        $userId = Auth::id();
        $user = User::with(['verificationDocuments' => function ($query) {
            $query->with('verificationStatut');
        }])->findOrFail($userId);

        $is_hote = $user->is_hote;

        $verificationDocumentsWithStatus = $user->verificationDocuments->map(function ($verificationDocument) {
            return [
                'document_id' => $verificationDocument->id,
                'document_name' => $verificationDocument->document ? $verificationDocument->document->name : null,
                'path' => $verificationDocument->path,
                'status' => $verificationDocument->verificationStatut ? $verificationDocument->verificationStatut->status : null,
            ];
        });

        $requestStatus = $is_hote ? 'Validé' : 'Non validé';

        return response()->json([
            'verification_documents' => $verificationDocumentsWithStatus,
            'Statut_demande' => $requestStatus
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Une erreur est survenue lors de la récupération des documents de vérification.'], 500);
    }
}

/**
 * @OA\Post(
 *     path="/api/verificationdocument/update",
 *     summary="Changer un document de vérification",
 *     tags={"verification_document"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données pour changer le document de vérification",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="verification_document_id",
 *                     description="ID du document de vérification à changer",
 *                     type="integer"
 *                 ),
 *                 @OA\Property(
 *                     property="new_document",
 *                     description="Nouveau document à téléverser",
 *                     type="string",
 *                     format="binary"
 *                 ),
 *                 required={"verification_document_id", "new_document"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document changé avec succès"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Impossible de changer le document"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors du changement de document"
 *     )
 * )
 */


public function changeDocument(Request $request)
{
    $data = $request->validate([
        'verification_document_id' => 'required|integer',
        'new_document' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    //$user_id = Auth::id();
    $user_id = 2;
    $verification_document_id = $data['verification_document_id'];
    $new_document = $data['new_document'];

    try {
        $verificationDocument = verification_document::findOrFail($verification_document_id);
        
        if ($verificationDocument->verificationStatut->status === 0) {
            $filename = basename($verificationDocument->path);
            $oldDocumentPath = public_path('image/document_verification/' . $filename);
                  if (file_exists($oldDocumentPath)) {
                         unlink($oldDocumentPath);
                    }

            $path_name = uniqid() . '.' . $new_document->getClientOriginalExtension();
            $path_url = url('/image/document_verification/' . $path_name);
            $new_document->move(public_path('image/document_verification'), $path_name);

            $verificationDocument->path = $path_url;
            $verificationDocument->save();

            return response()->json(['message' => 'Document changé avec succès.'], 200);
        } else {
            return response()->json(['error' => 'Impossible de changer le document car il a déjà été validé.'], 400);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Une erreur est survenue lors du changement de document.'], 500);
    }
}


}
