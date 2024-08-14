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


class PreferenceController extends Controller
{
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
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $identity_profil_path = $request->file('icone')->move(public_path('image/iconePreference'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconePreference/' . $icone_name;
                    $preference->icone = $icone_url;
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
                Preference::whereId($id)->update(['name' => $request->name]);
                return response()->json(['data' => 'Préférence mise à jour avec succès.'], 200);
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
                
                if ($request->hasFile('icone')) {
                    $icone_name = uniqid() . '.' . $request->file('icone')->getClientOriginalExtension();
                    $icone_path = $request->file('icone')->move(public_path('image/iconePreference'), $icone_name);
                    $base_url = url('/');
                    $icone_url = $base_url . '/image/iconePreference/' . $icone_name;
                    
                    Preference::whereId($id)->update(['icone' => $icone_url]);
                    
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
           
                 $preference = Preference::whereId($id)->first();
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
                $preferences = Preference::whereId($id)->update(['is_deleted' => true]);        

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
            $preference = Preference::whereId($id)->update(['is_blocked' => true]);

            if (!$preference) {
                return response()->json(['error' => 'Préférence non trouvé.'], 404);
            }

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
            $preference = Preference::whereId($id)->update(['is_blocked' => false]);

            if (!$preference) {
                return response()->json(['error' => 'Préférence non trouvé.'], 404);
            }

            return response()->json(['data' => 'his type of propriety is unblock successfuly.'], 200);
    } catch(Exception $e) {    
          return response()->json(['error' => $e->getMessage()], 500);
    }


}


    /**
     * @OA\Put(
     *     path="/api/preference/makeVerified/{id}",
     *     summary="make verified an preference",
     *     tags={"Preference"},
     * security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the preference to verified",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference successfully verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Preference successfully verified")
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
    public function makeVerified($id)
{
    try {
        // Récupérer la préférence par son ID, ou échouer si elle n'existe pas
        $preference = Preference::findOrFail($id);

        if ($preference->is_verified) {
            return response()->json(['data' => 'Préférence déjà vérifiée.'], 200);
        }

        // Mettre à jour le statut de vérification
        $preference->is_verified = true;
        $preference->save();  // Assurez-vous de sauvegarder les changements

        // Récupérer la relation Housing_preference et la mettre à jour si elle existe
        $housingPreference = Housing_preference::where('preference_id', $id)->first();

        if ($housingPreference) {
            $housingPreference->update(['is_verified' => true]);

            // Envoyer un e-mail de notification
            $mail = [
                'title' => "Validation de la nouvelle préférence ajoutée au logement",
                'body' => "L'ajout de cette préférence : " . $preference->name . " a été validé par l'administrateur.",
            ];

            dispatch( new SendRegistrationEmail($housingPreference->housing->user->email, $mail['body'], $mail['title'], 2));
        }

        return response()->json(['data' => 'Préférence vérifiée avec succès.'], 200);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



}