<?php

namespace App\Http\Controllers;

use App\Jobs\NotificationWithFile;
use App\Jobs\SendRegistrationEmail;
use App\Models\verification_document;
use App\Models\verification_statut;
use App\Models\User;
use App\Models\Document;
use App\Models\Commission;
use App\Models\Right;
use Spatie\Permission\Models\Role;
use App\Models\User_right;
use App\Models\Notification;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\Setting;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;

class VerificationDocumentController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

/**
 * @OA\Get(
 *     path="/api/verificationdocument/index",
 *     summary="Récupérer la liste des documents de vérification par utilisateur",
 *     description="Récupère la liste des documents de vérification groupés par utilisateur avec leur statut.",
 *     operationId="listVerificationDocumentsByUser",
 *     tags={"Demande_hote"},
 * security={{"bearerAuth": {}}},
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
        $users = User::whereHas('verificationDocuments', function ($query) {
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
     *   path="/api/users/verificationdocument/store",
     *   summary="Ajouter des Documents de Vérification",
     *   description="Ajoute des documents de vérification avec des fichiers joints (images). Envoie des notifications aux administrateurs lorsqu'un document est ajouté.",
     *   tags={"Demande_hote"},
    *  security={{"bearerAuth": {}}},
     *   @OA\RequestBody(
     *     required=true,
     *     description="Requête avec des fichiers à uploader",
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         type="object",
     *         required={"id_document", "image_piece"},
     *         properties={
     *           @OA\Property(
     *             property="id_document",
     *             type="array",
     *             description="Liste des IDs des documents",
     *             @OA\Items(type="integer")
     *           ),
     *           @OA\Property(
     *             property="image_piece",
     *             type="array",
     *             description="Liste des fichiers image",
     *             @OA\Items(type="string", format="binary")
     *           ),
     *         }
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Documents créés avec succès",
     *     @OA\JsonContent(
     *       type="object",
     *       properties={
     *         @OA\Property(
     *           property="data",
     *           type="string",
     *           example="Verification documents créés avec succès."
     *         ),
     *         @OA\Property(
     *           property="verification_documents",
     *           type="array",
     *           description="Documents de vérification créés",
     *           @OA\Items(
     *             type="object",
     *             properties={
     *               @OA\Property(property="user_id", type="integer"),
     *               @OA\Property(property="document_id", type="integer"),
     *               @OA\Property(property="path", type="string", format="uri"),
     *             }
     *           )
     *         ),
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Mauvaise requête",
     *     @OA\JsonContent(
     *       type="object",
     *       properties={
     *         @OA\Property(
     *           property="error",
     *           type="string",
     *           example="Les tableaux id_document et image_piece doivent avoir la même longueur."
     *         ),
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Erreur du serveur",
     *     @OA\JsonContent(
     *       type="object",
     *       properties={
     *         @OA\Property(
     *           property="error",
     *           type="string",
     *           example="Erreur interne du serveur."
     *         ),
     *       }
     *     )
     *   )
     * )
     */



     public function store(Request $request)
     {
         // Définir les règles de validation
         $rules = [
             'id_document' => 'required|array',
             'id_document.*' => 'required|integer|exists:documents,id', // Assurez-vous que chaque id_document existe dans la table documents
             'image_piece' => 'required|array',
             'image_piece.*' => 'required' // Limitez la taille du fichier à 2MB et acceptez uniquement les formats d'image
         ];

         // Définir les messages d'erreur personnalisés
         $messages = [
             'id_document.required' => 'Les documents sont obligatoires.',
             'id_document.array' => 'Les documents doivent être sous forme de tableau.',
             'id_document.*.required' => 'Chaque document est obligatoire.',
             'id_document.*.integer' => 'Chaque document doit être un entier.',
             'id_document.*.exists' => 'Le document sélectionné n\'existe pas.',
             'image_piece.required' => 'Les images des documents sont obligatoires.',
             'image_piece.array' => 'Les images des documents doivent être sous forme de tableau.',
             'image_piece.*.required' => 'Chaque image de document est obligatoire.',
             'image_piece.*.image' => 'Chaque fichier doit être une image.',
         ];

         // Valider les données
         $validator = Validator::make($request->all(), $rules, $messages);

         // Vérifier si la validation échoue
         if ($validator->fails()) {
             return response()->json(['error' => $validator->errors()], 422);
         }

         try {
             $user = auth()->user();
             $user_id = $user->id;
             $user_name = $user->lastname;
             $user_firstname = $user->firstname;
             $idDocuments = $request->id_document;
             $imagePieces = $request->file('image_piece');

             if (count($idDocuments) !== count($imagePieces)) {
                 return response()->json(['error' => 'Les tableaux id_document et image_piece doivent avoir la même longueur.'], 200);
             }

             $verificationDocuments = [];

             foreach ($idDocuments as $key => $idDocument) {
                 $existingDocument = verification_document::where('user_id', $user_id)
                     ->where('document_id', $idDocument)
                     ->first();

                 if ($existingDocument) {
                     return response()->json([
                         'error' => "Le document avec l'identifiant $idDocument a déjà été soumis par cet utilisateur."
                     ], 200);
                 }

                 $imagePiece = $imagePieces[$key];
                 $identity_profil_url = '';
                 $identity_profil_url = $this->fileService->uploadFiles($imagePiece, 'image/document_verification', 'extensionDocumentImage');
                 if ($identity_profil_url['fails']) {
                    //  return (new ServiceController())->apiResponse(404, [], $identity_profil_url[0]['result']);
                    }

                 $verificationDocument = new verification_document();
                 $verificationDocument->user_id = $user_id;
                 $verificationDocument->document_id = $idDocument;
                 $verificationDocument->path = $identity_profil_url['result'];
                 $verificationDocument->save();

                 $verificationStatut = new verification_statut();
                 $verificationStatut->verification_document_id = $verificationDocument->id;
                 $verificationStatut->save();

                 $filePaths[] = $identity_profil_url;
                 $verificationDocuments[] = $verificationDocument;
             }

             $adminRole = DB::table('rights')->where('name', 'admin')->first();

             if (!$adminRole) {
                 return response()->json(['message' => 'Le rôle d\'admin n\'a pas été trouvé.'], 404);
             }

             $adminUsers = User::whereHas('user_right', function ($query) use ($adminRole) {
                 $query->where('right_id', $adminRole->id);
             })
             ->get();

             foreach ($adminUsers as $adminUser) {
                 $notification = new Notification();
                 $notification->user_id = $adminUser->id;
                 $notification->name = "Une demande d'être hôte vient d'être envoyée par $user_name $user_firstname.";
                 $notification->save();

                 $mail = [
                     'title' => 'Demande d\'être hôte',
                     'body' => "Une demande d'être hôte vient d'être envoyée par $user_name $user_firstname. Les documents fournis sont en pièce jointe. Cliquez sur le lien suivant pour valider la demande : https://gethouse.com/validation/"
                 ];

                 try {
                    //  Mail::to($adminUser->email)->send(new NotificationEmail($mail, $filePaths));

                     dispatch(new NotificationWithFile($adminUser->email, $mail['body'], $mail['title'],$filePaths));

                 } catch (\Exception $e) {
                     // Gérer l'erreur de l'envoi de l'email
                 }
             }

             return response()->json(['message' => 'Documents de vérification créés avec succès.', 'verification_documents' => $verificationDocuments], 201);
         } catch (Exception $e) {
             return response()->json(['error' => 'Une erreur est survenue', 'message' => $e->getMessage()], 500);
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
   *     tags={"Demande_hote"},
   * security={{"bearerAuth": {}}},
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
      * security={{"bearerAuth": {}}},
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
    $user_exist = User::where('id', $user_id )->exists();
    if (!$user_exist) {
        return response()->json(['error' => "ID de l'utilisateur  invalides."], 400);
    }
    if (!$verificationDocumentsExist) {
        return response()->json(['error' => 'IDs de documents de vérification invalides.'], 400);
    }

    try {
        foreach ($verification_document_ids as $verification_document_id) {
            $verificationStatut = verification_statut::find($verification_document_id);
            //$verificationStatut = verification_statut::where('verification_document_id', $verification_document_id)->first();
            if ($verificationStatut) {
                //$verificationStatut->update(['status' => 1]);
                $verificationStatut->status = 1;
                $verificationStatut->save();
            }
        }

        $user = User::findOrFail($user_id);
        $role = Role::where('name','hote')->first();
        $grant = new AuthController();
        $user_hote = $grant->assignRoleToUser($request,$user_id,$role->id);
        $notification = new Notification();
        $notification->user_id = $user_id;
        $notification->name = "Votre demande d'être hôte a été validée avec succès.";
        $notification->save();
        $commission=new Commission();
        $commission->user_id=$user->id;
        $commission->valeur = Setting::first()->commission_hote_defaut??5;
        $commission->save();
        $mail = [
            'title' => 'Demande d\'être hôte',
            'body' => "Votre demande d'être hôte a été validée avec succès."
        ];


         dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 1));

        return response()->json(['message' => 'Documents validés avec succès et notification envoyée.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


/**
 * @OA\Post(
 *     path="/api/verificationdocument/hote/valider/one",
 *     summary="Valider un document de vérification pour devenir hôte(ici on valide document par document)",
 *     tags={"Validation documents pour etre hôte"},
 *     security={{"bearerAuth": {}}},
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

        $user_exist = User::where('id', $user_id )->exists();
        if (!$user_exist) {
            return response()->json(['error' => "ID de l'utilisateur  invalides."], 200);
        }
        if($verificationStatut->status == 1){
            return response()->json(['error' => "Ce document a déjà été validé. Vous ne pouvez plus le revalider."], 200);
        }

        $verificationStatut->update(['status' => 1]);

        $user = User::findOrFail($user_id);
        $allDocumentsValidated = $user->verificationDocuments()->whereHas('verificationStatut', function ($query) {
            $query->where('status', 0);
        })->count() === 0;

        if ($allDocumentsValidated) {

            $role = Role::where('name','hote')->first();
            $grant = new AuthController();
            $user_hote = $grant->assignRoleToUser($request,$user_id,$role->id);

            $commission=new Commission();
            $commission->user_id=$user->id;
            $commission->valeur = Setting::first()->commission_hote_defaut??5;

            $commission->save();
            // dd('salut');
            $mail = [
                'title' => 'Demande d\'être hôte',
                'body' => "Votre demande d'être hôte a été validée avec succès."
            ];
             dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));

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
 *     tags={"Demande_hote"},
 * security={{"bearerAuth": {}}},
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

        $right = Right::where('name','hote')->first();
        $exist = User_right::where('user_id',$userId)->where('right_id',$right->id)->exists();

        $verificationDocumentsWithStatus = $user->verificationDocuments->map(function ($verificationDocument) {
            return [
                'document_id' => $verificationDocument->id,
                'document_name' => $verificationDocument->document ? $verificationDocument->document->name : null,
                'path' => $verificationDocument->path,
                'status' => $verificationDocument->verificationStatut ? $verificationDocument->verificationStatut->status : null,
            ];
        });

        $requestStatus = $exist ? 'Validé' : 'Non validé';

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
 *     path="/api/users/verificationdocument/update",
 *     summary="Changer un document de vérification",
 *     tags={"Demande_hote"},
 * security={{"bearerAuth": {}}},
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
        'new_document' => 'required',
    ]);

    $user_id = Auth::id();
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

                $identity_profil_url = '';
                $identity_profil_url = $this->fileService->uploadFiles($new_document, 'image/document_verification', 'extensionDocumentImage');
                if ($identity_profil_url['fails']) {
                    // return (new ServiceController())->apiResponse(404, [], $identity_profil_url[0]['result']);
                }

            $verificationDocument->path = $identity_profil_url['result'];
            $verificationDocument->save();

            return response()->json(['message' => "Document changé avec succès."], 200);
        } else {
            return response()->json(['error' => 'Impossible de changer le document car il a déjà été validé.'], 400);
        }
    } catch (\Exception $e) {
        return response()->json(['error' =>$e->getMessage()], 500);
    }
}


}
