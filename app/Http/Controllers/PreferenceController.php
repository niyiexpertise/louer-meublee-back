<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Housing;
use App\Models\Preference;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File as F ;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use Illuminate\Validation\ValidationException ;
use Exception;
use Illuminate\Validation\Rule;
use App\Models\housing_preference;
use App\Models\User_preference;
use App\Services\FileService;

class PreferenceController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

        /**
     * @OA\Get(
     *     path="/api/preference/VerifiednotBlocknotDelete",
     *     summary="Get all preferences (verified, not blocked, not deleted)",
     *     tags={"Preference"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of preferences"
     *     )
     * )
     */
    public function VerifiednotBlocknotDelete()
    {
        try{
                $preferences = Preference::where('is_verified',true)->where('is_blocked', false)->where('is_deleted', false)->get();
                return response()->json(['data' => $preferences], 200);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

          /**
     * @OA\Get(
     *     path="/api/preference/index",
     *     summary="Get all preferences (verified, not blocked, not deleted)",
     *     tags={"Preference"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of preferences"
     *     )
     * )
     */
    public function index()
    {
        try{
                $preferences = Preference::where('is_verified',true)->where('is_blocked', false)->where('is_deleted', false)->get();
                return response()->json(['data' => $preferences], 200);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

            /**
     * @OA\Get(
     *     path="/api/preference/VerifiedBlocknotDelete",
     *     summary="Get all preferences (verified, blocked, not deleted)",
     *     tags={"Preference"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of preferences"
     *     )
     * )
     */

    public function VerifiedBlocknotDelete(){
        try{
                $preferences = Preference::where('is_verified',true)->where('is_blocked', true)->where('is_deleted', false)->get();
            return response()->json(['data' => $preferences], 200);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }


                    /**
     * @OA\Get(
     *     path="/api/preference/VerifiednotBlockDelete",
     *     summary="Get all preferences (verified, not blocked,  deleted)",
     *     tags={"Preference"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of preferences"
     *     )
     * )
     */
    public function VerifiednotBlockDelete(){
        try{
                $preferences = Preference::where('is_verified',true)->where('is_blocked', false)->where('is_deleted', true)->get();
            return response()->json(['data' => $preferences], 200);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }


       /**
     * @OA\Get(
     *     path="/api/preference/indexUnverified",
     *     summary="Get all preferences unverified",
     *     tags={"Preference"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of preferences"
     *     )
     * )
     */
    public function indexUnverified(){
        try{
            $preferences = Preference::where('is_verified',false)->where('is_deleted', false)->get();
        return response()->json(['data' => $preferences], 200);

        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

 /**
         * @OA\Post(
         *     path="/api/preference/store",
         *     summary="Create a new preference ",
         *     tags={"Preference"},
         * security={{"bearerAuth": {}}},
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="name", type="string", example="salle de jeux"),
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
         *     @OA\Response(
         *         response=200,
         *         description="Preference  created successfully"
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Invalid credentials"
         *     )
         * )
         */

    public function store(Request $request)
    {
        try{
                $data = $request->validate([
                    'name' => 'required|unique:preferences|max:255',
                ]);
                $preference = new Preference();
                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $images = $request->file('icone');
                    if(!isset($images[0])){
                        return (new ServiceController())->apiResponse(404, [], 'L\'image n\'a  pas été correctement envoyé.');
                    }
                    $image =$images[0];
                    $identity_profil_url = $this->fileService->uploadFiles($image, 'image/iconePreference', 'extensionImage');;
                    if ($identity_profil_url['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
                    }
                    $preference->icone = $identity_profil_url['result'];
                    }
                $preference->name = $request->name;
                $preference->is_verified = true;
                $preference->save();
                return response()->json(['data' => 'Type de preference créé avec succès.', 'preference' => $preference], 201);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }








 /**
     * @OA\Get(
     *     path="/api/preference/show/{id}",
     *     summary="Get a specific preference by ID",
     *     tags={"Preference"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the preference",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try{
                $preference = Preference::find($id);

                if (!$preference) {
                    return response()->json(['error' => 'Préférence non trouvé.'], 404);
                }
                return response()->json(['data' => $preference], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

/**
     * @OA\Put(
     *     path="/api/preference/updateName/{id}",
     *     summary="Update a preference by ID",
     *     tags={"Preference"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the preference",
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="preference1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateName(Request $request, string $id)
    {
        try{
            $preference = Preference::find($id);
            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    Rule::unique('preferences')->ignore($id),
                ],
            ]);
            if(!$preference){
                return response()->json(['error' => 'Préférence non trouvé.'], 404);
            }
            // return response()->json(['error' =>$request->name ]);
            $preference->name = $request->name;
            $preference->save();                return response()->json(['data' => 'Préférence mise à jour avec succès.'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

/**
     * @OA\Post(
     *     path="/api/preference/updateIcone/{id}",
     *     summary="Update an preference icone by ID",
     *     tags={"Preference"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the preference to update",
     *         @OA\Schema(type="integer")
     *     ),
 * @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="icone",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *       )
 *     )
 *   ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Preference updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Preference not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function updateIcone(Request $request, string $id)
    {

        try {
            $preference = Preference::find($id);

            if (!$preference) {
                return response()->json(['error' => 'Preference non trouvé.'], 404);
            }

            // $request->validate([
            //         'icone' => 'image|mimes:jpeg,jpg,png,gif'
            //     ]);

            $oldProfilePhotoUrl = $preference->icone;
            if ($oldProfilePhotoUrl) {
                $parsedUrl = parse_url($oldProfilePhotoUrl);
                $oldProfilePhotoPath = public_path($parsedUrl['path']);
                if (F::exists($oldProfilePhotoPath)) {
                    F::delete($oldProfilePhotoPath);
                }
            }
                $identity_profil_url = '';
                if ($request->hasFile('icone')) {
                    $images = $request->file('icone');
                    if(!isset($images[0])){
                        return (new ServiceController())->apiResponse(404, [], 'L\'image n\'a  pas été correctement envoyé.');
                    }
                    $image =$images[0];
                    $identity_profil_url = $this->fileService->uploadFiles($image, 'image/iconePreference', 'extensionImage');;
                    if ($identity_profil_url['fails']) {
                        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
                    }
                    $preference->icone = $identity_profil_url['result'];
                    $preference->save();

                    return response()->json(['data' => 'icône de l\'équipement mis à jour avec succès.'], 200);
                } else {
                dd("h");
                return response()->json(['error' => 'Aucun fichier d\'icône trouvé dans la requête.'], 400);
            }
        } catch (QueryException $e) {
            return response()->json(['error' => 'Erreur de requête SQL: ' . $e->getMessage()], 500);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     /**
     * @OA\Delete(
     *     path="/api/preference/destroy/{id}",
     *     summary="Delete a preference by ID",
     *     tags={"Preference"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the preference",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Preference deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try{
            $preference = Preference::find($id);
                if (!$preference) {
                    return response()->json(['error' => 'Préférence non trouvé.'],200);
                }
                $housingPreferences = housing_preference::where('preference_id', $id)->exists();

                if ($housingPreferences) {
                    return response()->json(['message' => 'Suppression impossible car la preference est déja associé à un logement.'], 200);
                }
                $existingPreference = User_preference::where('preference_id',  $id)->exists();

                if ($existingPreference) {
                    return response()->json(['message' => 'Suppression impossible car la preference est déja associé à un utilisateur.'], 200);

                }
                $preference->is_deleted = true;
                $preference->save();
                return response()->json(['data' => 'Préférence supprimé avec succès.'], 200);
        } catch(Exception $e) {
              return response()->json(['error' => $e->getMessage()], 500);
        }

    }

        /**
 * @OA\Put(
 *     path="/api/preference/block/{id}",
 *     summary="Block a preference",
 *     tags={"Preference"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the preference to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Preference successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Preference successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Preference not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Preference not found")
 *         )
 *     )
 * )
 */

    public function block(string $id)
 {
    try{
        $preference = Preference::find($id);

            if (!$preference) {
                return response()->json(['error' => 'Préférence non trouvé.'], 404);
            }
            $preference->is_blocked = true;
            $preference->save();
            return response()->json(['data' => 'This type of propriety is block successfuly.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }


 }

  /**
 * @OA\Put(
 *     path="/api/preference/unblock/{id}",
 *     summary="Unblock a preference",
 *     tags={"Preference"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the preference to unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Preference successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Preference successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Preference not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Preference not found")
 *         )
 *     )
 * )
 */

 public function unblock(string $id)
{
    try{
        $preference = Preference::find($id);

            if (!$preference) {
                return response()->json(['error' => 'Préférence non trouvé.'], 404);
            }
            $preference->is_blocked = false;
            $preference->save();
            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
    }


}


   /**
 * @OA\Post(
 *     path="/api/preference/makeVerified",
 *     summary="Valider plusieurs préférences inexistantes des logements",
 *      tags={"Housing Preference"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="preference_ids",
 *                 type="array",
 *                 @OA\Items(type="integer"),
 *                 example={1, 2, 3}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Préférences vérifiées avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="verified",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     example={1, 3}
 *                 ),
 *                 @OA\Property(
 *                     property="already_verified",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     example={2}
 *                 ),
 *                 @OA\Property(
 *                     property="not_found",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     example={4}
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid preference IDs provided",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Invalid preference IDs provided")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Internal server error")
 *         )
 *     )
 * )
 */

 public function makeVerified(Request $request)
 {
     try {
         $request->validate([
             'preference_ids' => 'required'
         ]);

         
 
         $results = [];
 
         foreach ($request->preference_ids as $id) {
             try {
                 // Vérification de la validité de l'ID
                 if (!intval($id) || $id < 0) {
                     $results[] = [
                         'id' => $id,
                         'message' => 'Valeur invalide',
                         'status' => 'invalid_value',
                     ];
                     continue;
                 }
 
                 $preference = Preference::find($id);
 
                 if (!$preference) {
                     $results[] = [
                         'id' => $id,
                         'message' => 'Préférence non trouvée',
                         'status' => 'not_found',
                     ];
                     continue;
                 }
 
                 if ($preference->is_verified) {
                     $results[] = [
                         'id' => $id,
                         'message' => 'Préférence déjà vérifiée',
                         'status' => 'already_verified',
                     ];
                     continue;
                 }
 
                 // Marquer la préférence comme vérifiée
                 $preference->is_verified = true;
                 $preference->save();
 
                 $housingPreference = Housing_preference::where('preference_id', $id)->first();
                 if ($housingPreference) {
                     $housingPreference->is_verified = true;
                     $housingPreference->save();
 
                     $mail = [
                         'title' => "Validation de la nouvelle préférence ajoutée au logement",
                         'body' => "L'ajout de cette préférence : " . $preference->name . " a été validé par l'administrateur.",
                     ];
 
                     dispatch(new SendRegistrationEmail($housingPreference->housing->user->email, $mail['body'], $mail['title'], 2));
                 }
 
                 $results[] = [
                     'id' => $id,
                     'message' => 'Préférence vérifiée avec succès',
                     'status' => 'verified',
                 ];
             } catch (Exception $e) {
                 $results[] = [
                     'id' => $id,
                     'message' => 'Erreur lors de la vérification de la préférence',
                     'status' => 'error',
                 ];
             }
         }
 
         $successfulValidations = array_filter($results, function ($result) {
            return $result['status'] === 'verified';
        });

        if (!empty($successfulValidations)) {
            return (new ServiceController())->apiResponse(200,$results,'Validé avec succès');
        }


        return (new ServiceController())->apiResponse(404,$results,'Aucune association vérifiée ou toutes étaient déjà vérifiées.');
     } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
     }
 }
 




}
