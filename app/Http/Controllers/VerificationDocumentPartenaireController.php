<?php

namespace App\Http\Controllers;

use App\Jobs\NotificationWithFile;
use App\Jobs\SendRegistrationEmail;
use App\Models\verification_document_partenaire;
use App\Models\verification_statut_partenaire;
use App\Models\User;
use App\Models\Document;
use App\Models\user_partenaire;
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
use Illuminate\Support\Facades\DB;
use App\Services\FileService;
class VerificationDocumentPartenaireController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

/**
 * @OA\Get(
 *     path="/api/verificationdocumentpartenaire/index",
 *     summary="Récupérer la liste des documents de vérification par utilisateur",
 *     description="Récupère la liste des documents de vérification groupés par utilisateur avec leur statut.",
 *     tags={"Demande_partenaire"},
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
        $users = User::whereHas('verificationDocumentspartenaire', function ($query) {
                         $query->whereHas('verificationStatutpartenaire', function ($query) {
                             $query->where('status', 0);
                         });
                     })
                     ->with('verificationDocumentspartenaire', 'verificationDocumentspartenaire.verificationStatutpartenaire', 'verificationDocumentspartenaire.document')
                     ->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'Aucun utilisateur avec des documents de vérification en attente trouvé.'], 404);
        }

        $verificationDocumentsByUser = [];

        foreach ($users as $user) {
            $code_promo = ""; // Initialiser le code promo pour chaque utilisateur
            $verificationDocumentspartenaire = $user->verificationDocumentspartenaire->map(function ($verificationDocument) use (&$code_promo) {
                $code_promo = $verificationDocument->code_promo;

                return [
                    'id_verification_document' => $verificationDocument->id,
                    'document_id' => $verificationDocument->document_id,
                    'document_name' => $verificationDocument->document ? $verificationDocument->document->name : null,
                    'path' => $verificationDocument->path,
                    'status' => $verificationDocument->verificationStatutpartenaire ? $verificationDocument->verificationStatutpartenaire->status : null,
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
                'verification_documents' => $verificationDocumentspartenaire,
                'code_promo' => $code_promo
            ];

            $verificationDocumentsByUser[] = $userInfo;
        }

        return response()->json(['data' => $verificationDocumentsByUser], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * @OA\Post(
 *   path="/api/users/verificationdocumentpartenaire/store",
 *   summary="Ajouter des Documents de Vérification",
 *   description="Ajoute des documents de vérification avec des fichiers joints (images). Envoie des notifications aux administrateurs lorsqu'un document est ajouté.",
 *   tags={"Demande_partenaire"},
 *   security={{"bearerAuth": {}}},
 *   @OA\RequestBody(
 *     required=true,
 *     description="Requête avec des fichiers à uploader",
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         required={"id_document", "image_piece", "code_promo"},
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
 *           @OA\Property(
 *             property="code_promo",
 *             type="string",
 *             description="Code promotionnel unique pour l'utilisateur"
 *           )
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
 *           property="message",
 *           type="string",
 *           example="Documents de vérification créés avec succès."
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
    try {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'id_document' => 'required|array',
            'image_piece' => 'required|array',
            'code_promo' => 'required|unique:verification_document_partenaires,code_promo',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = auth()->user();
        $user_id = $user->id;
        $user_name = $user->lastname;
        $user_firstname = $user->firstname;
        $idDocuments = $request->id_document;
        $codePromo = $request->code_promo;
        $imagePieces = $request->file('image_piece');

        if (count($idDocuments) !== count($imagePieces)) {
            return response()->json(['error' => 'Les tableaux id_document et image_piece doivent avoir la même longueur.'], 400);
        }

        $verificationDocuments = [];
        $filePaths = [];

        foreach ($idDocuments as $key => $idDocument) {
            $existingDocument = verification_document_partenaire::where('user_id', $user_id)
                ->where('document_id', $idDocument)
                ->first();

            if ($existingDocument) {
                return response()->json([
                    'error' => "Le document avec l'identifiant $idDocument a déjà été soumis par cet utilisateur."
                ], 400);
            }

            $imagePiece = $imagePieces[$key];

            $validationResultFile = $this->fileService->uploadFiles($imagePiece, 'image/document_verification','extensionDocumentImage');

            if ($validationResultFile['fails']) {
                return (new ServiceController())->apiResponse(404, [], $validationResultFile['result']);
            }

            $verificationDocument = new verification_document_partenaire();
            $verificationDocument->user_id = $user_id;
            $verificationDocument->document_id = $idDocument;
            $verificationDocument->path = $validationResultFile['result'];
            $verificationDocument->code_promo = $codePromo;
            $verificationDocument->save();

            $verificationStatut = new verification_statut_partenaire();
            $verificationStatut->vpdocument_id = $verificationDocument->id;
            $verificationStatut->save();


            $filePaths[] = $validationResultFile['result'];
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

            $mail = [
                'title' => 'Demande d\'être partenaire',
                'body' => "Une demande d'être partenaire vient d'être envoyée par $user_name $user_firstname. Les documents fournis sont en pièce jointe. "
            ];

            try {
                dispatch(new SendRegistrationEmail($adminUser->email, $mail['body'], $mail['title'],2));

            } catch (\Exception $e) {

            }
        }

        return (new ServiceController())->apiResponse(200, $verificationDocuments, 'Documents de vérification créés avec succès.');

       
    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], 'Une erreur est survenue', 'message' .$e->getMessage());
    }
}


     /**
   * @OA\Get(
   *     path="/api/verificationdocumentpartenaire/show/{id}",
   *     summary="Afficher les detail d'une demande pour être partenaire",
   *     tags={"Demande_partenaire"},
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
   *         description="Information detaillée e la demande pour être partenaire"
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
                      ->whereHas('verificationDocumentspartenaire')
                      ->with('verificationDocumentspartenaire', 'verificationDocumentspartenaire.verificationStatutpartenaire', 'verificationDocumentspartenaire.document')
                      ->firstOrFail();

          $verificationDocuments = $user->verificationDocumentspartenaire->map(function ($verificationDocument) {
              return [
                  'id_verification_document' => $verificationDocument->id,
                  'document_id' => $verificationDocument->document_id,
                  'document_name' => $verificationDocument->document ? $verificationDocument->document->name : null,
                  'path' => $verificationDocument->path,
                  'status' => $verificationDocument->verificationStatutpartenaire ? $verificationDocument->verificationStatutpartenaire->status : null,
              ];
          });

          $code_promo = $user->verificationDocumentspartenaire->first()->code_promo;

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
              'photo_profile' => $user->file_profil,
              'verification_documents' => $verificationDocuments,
              'code_promo' => $code_promo
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
     *     path="/api/verificationdocumentpartenaire/partenaire/valider/all",
     *     summary="Valider les documents en un coup ,bref valider tout en un clic",
     *     description="Valide les documents de vérification pour un utilisateur .",
     *     tags={"Validation documents pour etre partenaire"},
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
    $verificationDocumentsExist = verification_statut_partenaire::whereIn('vpdocument_id', $verification_document_ids)->exists();

    if (!$verificationDocumentsExist) {
        return response()->json(['error' => 'IDs de documents de vérification invalides.'], 400);
    }
    $user_exist = User::where('id', $user_id )->exists();
    if (!$user_exist) {
        return response()->json(['error' => "ID de l'utilisateur  invalides."], 400);
    }


    try {
        foreach ($verification_document_ids as $verification_document_id) {
            $verificationStatut = verification_statut_partenaire::where('vpdocument_id',$verification_document_id)->first();
           // $verificationStatut = verification_statut_partenaire::where('vpdocument_id', $verification_document_id)->first();
            if ($verificationStatut) {
                $verificationStatut->status = 1;
                $verificationStatut->save();
                //$verificationStatut->update(['status' => 1]);
            }
        }
        $verificationDocumentsExist = verification_statut_partenaire::whereIn('vpdocument_id', $verification_document_ids)->get();
        $user = User::findOrFail($user_id);
        $role = Role::where('name','partenaire')->first();
        $grant = new AuthController();
        $user_hote = $grant->assignRoleToUser($request,$user_id,$role->id);
        $commission=new user_partenaire();
        $commission->user_id=$user->id;
        $commission->commission = Setting::first()->commission_partenaire_defaut ?? 5;
        $commission->reduction_traveler = Setting::first()->reduction_partenaire_defaut ?? 3;
        $commission->number_of_reservation = Setting::first()->number_of_reservation_partenaire_defaut ?? 3;
        $commission->code_promo=$verificationStatut->verificationDocumentpartenaire->code_promo;
        $commission->save();
        $mail = [
            'title' => 'Demande d\'être partenaire',
            'body' => "Votre demande d'être partenaire a été validée avec succès."
        ];

       

        return (new ServiceController())->apiResponse(200, [], 'Documents validés avec succès.');
        dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));
    } catch (\Exception $e) {
        return response()->json(['error' =>  $e->getMessage()], 500);
    }
}


/**
 * @OA\Post(
 *     path="/api/verificationdocumentpartenaire/partenaire/valider/one",
 *     summary="Valider un document de vérification pour devenir partenaire(ici on valide document par document)",
 *     tags={"Validation documents pour etre partenaire"},
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
        $verificationStatut = verification_statut_partenaire::where('vpdocument_id', $verification_document_id)->first();
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
        $allDocumentsValidated = $user->verificationDocumentspartenaire()->whereHas('verificationStatutpartenaire', function ($query) {
            $query->where('status', 0);
        })->count() === 0;

        if ($allDocumentsValidated) {

            $role = Role::where('name','partenaire')->first();
            $grant = new AuthController();
            $user_hote = $grant->assignRoleToUser($request,$user_id,$role->id);

            $commission=new user_partenaire();
            $commission->user_id=$user->id;
            
            $commission->commission = Setting::first()->commission_partenaire_defaut ?? 5;
            $commission->reduction_traveler = Setting::first()->reduction_partenaire_defaut ?? 3;
                $commission->number_of_reservation = Setting::first()->number_of_reservation_partenaire_defaut ?? 3;
            $commission->code_promo=$verificationStatut->verificationDocumentpartenaire->code_promo;
            $commission->save();
            // dd('salut');
            $mail = [
                'title' => 'Demande d\'être hôte',
                'body' => "Votre demande d'être partenaire a été validée avec succès."
            ];

             dispatch( new SendRegistrationEmail($user->email, $mail['body'], $mail['title'], 2));

        }

        return (new ServiceController())->apiResponse(200, [], 'Document validé avec succès.');

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * @OA\Get(
 *     path="/api/users/result/demandepartenaire",
 *     summary="Affiche pour un utiliateur connecté,le statut de ses documents soumis et le statut de sa demande",
 *     tags={"Demande_partenaire"},
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
         $user = User::with(['verificationDocumentspartenaire' => function ($query) {
             $query->with('verificationStatutpartenaire');
         }])->findOrFail($userId);

         $right = Right::where('name', 'partenaire')->first();
         $exist = User_right::where('user_id', $userId)->where('right_id', $right->id)->exists();


         $code_promo = "";

         $verificationDocumentsWithStatus = $user->verificationDocumentspartenaire->map(function ($verificationDocument) use (&$code_promo) {

             $code_promo = $verificationDocument->code_promo;
             return [
                 'document_id' => $verificationDocument->id,
                 'document_name' => $verificationDocument->document ? $verificationDocument->document->name : null,
                 'path' => $verificationDocument->path,
                 'status' => $verificationDocument->verificationStatutpartenaire ? $verificationDocument->verificationStatutpartenaire->status : null,
             ];
         });

         $requestStatus = $exist ? 'Validé' : 'Non validé';

         $data = [
            'verification_documents' => $verificationDocumentsWithStatus,
            'Statut_demande' => $requestStatus,
            'code_promo' => $code_promo,
         ];

         return (new ServiceController())->apiResponse(200, $data, "Liste des documents soumises par l'utilisateur connecté et leur statut ainsi que le statu de de sa demande");

        //  return response()->json([
        //      'verification_documents' => $verificationDocumentsWithStatus,
        //      'Statut_demande' => $requestStatus,
        //      'code_promo' => $code_promo,
        //  ], 200);
     } catch (\Exception $e) {
         return response()->json(['error' => $e->getMessage()], 500);
     }
 }


/**
 * @OA\Post(
 *     path="/api/users/verificationdocumentpartenaire/update",
 *     summary="Changer un document de vérification",
 *     tags={"Demande_partenaire"},
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

    try {

        $data = $request->validate([
            'verification_document_id' => 'required|integer',
            'new_document' => 'required',
        ]);
    
        $user_id = Auth::id();
        $verification_document_id = $data['verification_document_id'];
        $new_document = $data['new_document'][0];
        $verificationDocument = verification_document_partenaire::findOrFail($verification_document_id);
      

        if ($verificationDocument->verificationStatutpartenaire->status === 0) {
            $filename = basename($verificationDocument->path);
            $oldDocumentPath = public_path('image/document_verification/' . $filename);
                  if (file_exists($oldDocumentPath)) {
                         unlink($oldDocumentPath);
                    }
                    $identity_profil_url = '';
                    $identity_profil_url = $this->fileService->uploadFiles($new_document, 'image/document_verification', 'extensionDocumentImage');;
                    if ($identity_profil_url['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
                    }
                  

            $verificationDocument->path = $identity_profil_url['result'];
            $verificationDocument->save();

            return (new ServiceController())->apiResponse(200, [], 'Document changé avec succès.');

            // return response()->json(['message' => 'Document changé avec succès.'], 200);
        } else {
            return (new ServiceController())->apiResponse(404, [], 'Impossible de changer le document car il a déjà été validé.');
            // return response()->json(['error' => 'Impossible de changer le document car il a déjà été validé.'], 400);
        }
    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}

/**
 * @OA\Get(
 *     path="/api/partenaire/getPartenaires",
 * security={{"bearerAuth": {}}},
 *     summary="Obtenir la liste des partenaires ",
 *     description="Retourne les partenaires ",
 *     operationId="getPartenaires",
 *     tags={"Demande_partenaire"},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des partenaires récupérée avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="message", type="string", example="Détail du système de stockage")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la récupération des partenaires",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="integer", example=500),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     )
 * )
 */



public function getPartenaires(){
    try {

        $userPartenaires = user_partenaire::all();
        $data = [];

        foreach($userPartenaires as $userPartenaire){
            $userPartenaire->user->code_promo = $userPartenaire->code_promo;
            $userPartenaire->user->commission = $userPartenaire->commission;
            $userPartenaire->user->reduction_traveler = $userPartenaire->reduction_traveler;
            $userPartenaire->user->number_of_reservation = $userPartenaire->number_of_reservation;


            $data[] = $userPartenaire->user;
        }

        return (new ServiceController())->apiResponse(200, $data, "Liste des partenaires");
    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
}


}


