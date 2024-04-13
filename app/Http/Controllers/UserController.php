<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\User_role;
use App\Models\User_language;
use App\Models\Review;
use App\Models\Language;
use App\Models\Notification;
use App\Models\Commission;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/users/index",
 *     summary="Get all users",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of users",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="lastname", type="string", example="Doe"),
 *                 @OA\Property(property="firstname", type="string", example="John"),
 *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *                 @OA\Property(property="code_pays", type="string", example="FR"),
 *                 @OA\Property(property="telephone", type="string", example="0123456789"),
 *                 @OA\Property(property="country", type="string", example="France"),
 *                 @OA\Property(property="file_profil", type="string", example="profile.jpg"),
 *                 @OA\Property(property="piece_of_identity", type="string", example="id_card.jpg"),
 *                 @OA\Property(property="city", type="string", example="Paris"),
 *                 @OA\Property(property="address", type="string", example="123 Rue de la Liberté"),
 *                 @OA\Property(property="sexe", type="string", example="M"),
 *                 @OA\Property(property="postal_code", type="string", example="75001"),
 *                 @OA\Property(property="is_deleted", type="boolean", example=false),
 *                 @OA\Property(property="is_blocked", type="boolean", example=false),
 *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-04-03 10:00:00"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01 08:00:00"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-02-01 09:00:00"),
 *                 @OA\Property(
 *                     property="user_role",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="name", type="string", example="Admin"),
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="user_language",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="language_id", type="integer", example=1),
 *                         @OA\Property(property="name", type="string", example="French"),
 *                         @OA\Property(property="icone", type="string", example="fr.png")
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="user_preference",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="preference_id", type="integer", example=1),
 *                         @OA\Property(property="name", type="string", example="Theme"),
 *                         @OA\Property(property="icone", type="string", example="theme.png")
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */

    public function index()
{
    $users = User::with([
        'user_language.language',
        'user_preference.preference'
    ])
    ->where('is_deleted', false)
    ->get();
     
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUser = [
            'id' => $user->id,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'email' => $user->email,
            'code_pays' => $user->code_pays,
            'telephone' => $user->telephone,
            'country' => $user->country,
            'file_profil' => $user->file_profil,
            'piece_of_identity' => $user->piece_of_identity,
            'city' => $user->city,
            'address' => $user->address,
            'sexe' => $user->sexe,
            'postal_code' => $user->postal_code,
            'is_hote' => $user->is_hote,
            'is_traveller' => $user->is_traveller,
            'is_admin' => $user->is_admin,
            'is_deleted' => $user->is_deleted,
            'is_blocked' => $user->is_blocked,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'user_role' => User::find($user->id)->getRoleNames(),
            'user_language' => [],
            'user_preference' => [], 
        ];

        foreach ($user->user_language as $userLanguage) {
            $formattedUserLanguage = [
                'language_id' => $userLanguage->language_id,
                'name' => $userLanguage->language->name,
                'icone' => $userLanguage->language->icone,
            ];
            $formattedUser['user_language'][] = $formattedUserLanguage;
        }

        foreach ($user->user_preference as $userPreference) {
            $formattedUserPreference = [
                'preference_id' => $userPreference->preference_id,
                'name' => $userPreference->preference->name,
                'icone' => $userPreference->preference->icone,
            ];
            $formattedUser['user_preference'][] = $formattedUserPreference;
        }

        $formattedUsers[] = $formattedUser;
    }

    return response()->json(['date' => $formattedUsers], 200);
}
/**
 * @OA\Post(
 *   path="/api/users/register",
 *   tags={"User"},
 * security={{"bearerAuth": {}}},
 *   summary="Enregistrer un nouvel utilisateur",
 *   description="Enregistre un nouvel utilisateur avec les informations fournies",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(property="nom", type="string", example="Doe"),
 *         @OA\Property(property="prenom", type="string", example="John"),
 *         @OA\Property(
 *           property="password",
 *           type="string",
 *           format="password",
 *           example="Password123",
 *           minLength=8,
 *           description="Mot de passe (min : 8 caractères, une majuscule, un chiffre, un caractère spécial)"
 *         ),
 *         @OA\Property(property="code_pays", type="string", example="FR"),
 *         @OA\Property(property="telephone", type="string", example="1234567890"),
 *         @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *         @OA\Property(property="pays", type="string", example="France"),
 *         @OA\Property(
 *           property="identity_profil",
 *           type="string",
 *           format="binary",
 *           description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *         @OA\Property(property="ville", type="string", example="Paris"),
 *         @OA\Property(property="addresse", type="string", example="123 Rue de la Paix"),
 *         @OA\Property(property="sexe", type="string", example="Masculin"),
 *         @OA\Property(property="postal_code", type="string", example="75001"),
 *         @OA\Property(property="langage_id", type="string", example="[1,2,4]"),
 *         @OA\Property(
 *           property="password_confirmation",
 *           type="string",
 *           format="password",
 *           example="Password123",
 *           description="Confirmation du mot de passe (doit correspondre au mot de passe)"
 *         ),
 *         required={"nom", "prenom", "password", "code_pays", "telephone", "email", "pays", "ville", "addresse", "sexe", "postal_code", "language_id", "password_confirmation"}
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=201,
 *     description="Utilisateur enregistré avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Utilisateur enregistré avec succès")
 *     )
 *   ),
 *   @OA\Response(
 *     response=400,
 *     description="Erreur de validation",
 *     @OA\JsonContent(
 *       @OA\Property(property="errors", type="object", additionalProperties={"type": "string"})
 *     )
 *   )
 * )
 */

    public function register(Request $request)
    {
        
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'code_pays' => 'required|string',
            'telephone' => 'required|String|numeric|unique:users',
            'email' => 'required|email|unique:users',
            'pays' => 'required|string',
            'identity_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ville' => 'required|string',
            'addresse' => 'required|string',
            'sexe' => 'required|string',
            'language_id' => [
                'required',
                'min:1',
                'exists:languages,id'
                
            ],
            'password_confirmation' => 'required|string',
            
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        if ($request->hasFile('identity_profil')) {
        $identity_profil_name = uniqid() . '.' . $request->file('identity_profil')->getClientOriginalExtension();
        $identity_profil_path = $request->file('identity_profil')->move(public_path('image/photo_profil'), $identity_profil_name);
        $base_url = url('/');
        $identity_profil_url = $base_url . '/image/photo_profil/' . $identity_profil_name;
        }
        $user = new User([
            'lastname' => strtoupper($request->nom),
            'firstname' => $request->prenom,
            'password' => bcrypt($request->password),
            'telephone' => $request->telephone,
            'code_pays' => $request->code_pays,
            'email' => $request->email,
            'country' => $request->pays,
            'file_profil' => $identity_profil_url,
            'city' => $request->ville,
            'address' => $request->addresse,
            'sexe' => $request->sexe,
            'postal_code' => $request->postal_code,
            'is_admin' => 0,
            'is_hote' => 0,
            'is_traveller' => 1
            
            
        ]);

        $user->save();
        $user->assignRole('traveler');
        $userLanguages = $request->language_id;
        
        foreach ($userLanguages as $language_id) {
            $userLanguage = new User_language([
                'user_id' => $user->id,
                'language_id' => $language_id,
            ]);

            $userLanguage->save();
        }
        $created_at = $user->created_at;
        $date_creation = Carbon::parse($created_at)->isoFormat('D MMMM YYYY [à] HH[h]mm');
        $message_notification = "Compte créé avec succès le " . $date_creation;

           $notification = new Notification([
            'name' => $message_notification,
            'user_id' =>$user->id,
            
        ]);
        $notification->save();

        $user->save();
        return response()->json(['message' => 'User registered successfully','users'=>$user], 201);
    }


/**
 * @OA\Delete(
 *   path="/api/users/destroy/{id}",
 *   tags={"User"},
 * security={{"bearerAuth": {}}},
 *   summary="Marquer un utilisateur comme supprimé",
 *   description="Marque un utilisateur comme supprimé en définissant is_deleted à true.",
 *  @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
 *   @OA\Response(
 *     response=200,
 *     description="Utilisateur marqué comme supprimé avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Utilisateur marqué comme supprimé avec succès")
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Utilisateur non trouvé",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="string", example="Utilisateur non trouvé")
 *     )
 *   )
 * )
 */

    public function destroy(string $id)
  {
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'Utilisateur non trouvé'], 404);
    }
    $user->is_deleted = true;
    $user->save();

    return response()->json(['message' => 'Utilisateur marqué comme supprimé avec succès'], 200);
  }

/**
 * @OA\Get(
 *     path="/api/users/userReviews",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     summary="Obtenir les avis de l'utilisateur connecté",
 *     description="Récupère les avis associés à l'utilisateur connecté.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des avis de l'utilisateur connecté",
 *         @OA\JsonContent(
 *            
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé"
 *     )
 * )
 */
public function userReviews()
{

    $userId = Auth::id();
    $reviews = Review::where('user_id', $user_Id)->get();

    return response()->json([
        'data' => $reviews
    ]);
}

/**
 * @OA\Get(
 *     path="/api/users/userLanguages",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     summary="Obtenir les langues de l'utilisateur connecté",
 *     description="Récupère les langues associées à l'utilisateur connecté.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Langues de l'utilisateur connecté",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé"
 *     )
 * )
 */
public function userLanguages()
{
    $userId = Auth::id();
    $user = User::with('user_language.language')->find($user_Id);

    if (!$user) {
        return response()->json(['error' => 'Utilisateur non trouvé'], 404);
    }

    $languages = $user->user_language->map(function ($userLanguage) {
        return $userLanguage->language->name;
    });

    return response()->json([
        'data' => $languages
    ]);
}

/**
 * @OA\Get(
 *     path="/api/users/userPreferences",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     summary="Afficher les préférences de l'utilisateur connecté",
 *     description="Récupère les préférences de l'utilisateur connecté.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des préférences de l'utilisateur connecté",
 *         @OA\JsonContent(
 *         
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé"
 *     )
 * )
 */
public function showUserPreferences()
    {
        
        $userId = Auth::id();
        $user = User::findOrFail($user_Id);

        $userPreferences = $user->user_preference()->with('preference')->get();

        return response()->json([
            'data' => $userPreferences,
        ]);
    }

    /**
 * @OA\Post(
 *   path="/api/users/update_profile_photo",
 *   tags={"User"},
 * security={{"bearerAuth": {}}},
 *   summary="Mettre à jour la photo de profil de l'utilisateur",
 *   description="Permet à l'utilisateur de mettre à jour sa photo de profil en téléchargeant une nouvelle image",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="profile_photo",
 *           type="string",
 *           format="binary",
 *           description="Nouvelle photo de profil de l'utilisateur (JPEG, PNG, JPG, GIF, taille max : 2048)"
 *         ),
 *         required={"profile_photo"}
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Photo de profil mise à jour avec succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Photo de profil mise à jour avec succès"),
 *     )
 *   ),
 *   @OA\Response(
 *     response=400,
 *     description="Erreur de validation",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="object", additionalProperties={"type": "string"})
 *     )
 *   )
 * )
 */

 public function updateProfilePhoto(Request $request)
 {
     $userId = Auth::id();
     if (!$userId) {
         return response()->json(['error' => 'Unauthenticated'], 401);
     }
 
     $user = User::find($userId);
 
     if (!$user) {
         return response()->json(['error' => 'User not found'], 404);
     }

     $validator = Validator::make($request->all(), [
         'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
     ]);
 
     if ($validator->fails()) {
         return response()->json(['error' => $validator->errors()], 400);
     }
     $oldProfilePhotoUrl = $user->file_profil;
     if ($oldProfilePhotoUrl) {
         $parsedUrl = parse_url($oldProfilePhotoUrl);
         $oldProfilePhotoPath = public_path($parsedUrl['path']);
         if (File::exists($oldProfilePhotoPath)) {
             File::delete($oldProfilePhotoPath);
         }
     }
 
     $profilePhotoName = uniqid() . '.' . $request->file('profile_photo')->getClientOriginalExtension();
     $profilePhotoPath = $request->file('profile_photo')->move(public_path('image/photo_profil'), $profilePhotoName);
     $base_url = url('/');
     $user->file_profil = $base_url .'/image/photo_profil/' . $profilePhotoName;
     $user->save();
 
     return response()->json(['message' => 'Profile photo updated successfully', 'user' => $user], 200);
 }
 

         /**
 * @OA\Put(
 *     path="/api/users/block/{id}",
 *     summary="Block a user",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the users to block",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Users successfully blocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="User successfully blocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="User not found")
 *         )
 *     )
 * )
 */
    
 public function block(string $id)
 {
    try{
        $user = User::whereId($id)->update(['is_blocked' => true]);

        if (!$user) {
            return response()->json(['error' => 'User non trouvé.'], 404);
        }

        return response()->json(['data' => 'This user is block successfuly.'], 200);
    } catch(Exception $e) {
        return response()->json($e);
    }


 }

       /**
 * @OA\Put(
 *     path="/api/users/unblock/{id}",
 *     summary="Unblock a users",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the userto unblock",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User successfully unblocked",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="User successfully unblocked")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="User not found")
 *         )
 *     )
 * )
 */
public function unblock($id)
{
    try{
        $user= User::whereId($id)->update(['is_blocked' => false]);

        if (!$user) {
            return response()->json(['error' => 'User non trouvé.'], 404);
        }

        return response()->json(['data' => 'User débloqué avec succès.'], 200);
    }catch (Exception $e){
        return response()->json($e);
    }
}
/**
 * @OA\Get(
 *     path="/api/users/pays/{pays}",
 *     summary="Get all users from a country",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="pays",
 *         in="path",
 *         required=true,
 *         description="Code of the country",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of users from the specified country",
 *         @OA\JsonContent(
 *             
 *             
 *         )
 *     )
 * )
 */

 public function getUsersByCountry($country)
 {
     $users = User::with([
         'user_language.language',
         'user_preference.preference'
     ])
     ->where('country', $country)
     ->where('is_deleted', false)
     ->get();
     if ($users->isEmpty()) {
         return response()->json(['error' => 'Aucun utilisateur trouvé pour le pays spécifié.'], 404);
     }
 
     $formattedUsers = [];
     foreach ($users as $user) {
         $formattedUser = [
             'id' => $user->id,
             'lastname' => $user->lastname,
             'firstname' => $user->firstname,
             'email' => $user->email,
             'code_pays' => $user->code_pays,
             'telephone' => $user->telephone,
             'country' => $user->country,
             'file_profil' => $user->file_profil,
             'city' => $user->city,
             'address' => $user->address,
             'sexe' => $user->sexe,
             'postal_code' => $user->postal_code,
             'is_hote' => $user->is_hote,
            'is_traveller' => $user->is_traveller,
            'is_admin' => $user->is_admin,
             'is_deleted' => $user->is_deleted,
             'is_blocked' => $user->is_blocked,
             'email_verified_at' => $user->email_verified_at,
             'created_at' => $user->created_at,
             'updated_at' => $user->updated_at,
             'user_role' => User::find($user->id)->getRoleNames(),
             'user_language' => [],
             'user_preference' => [], 
         ];
         foreach ($user->user_language as $userLanguage) {
             $formattedUserLanguage = [
                 'language_id' => $userLanguage->language_id,
                 'name' => $userLanguage->language->name,
                 'icone' => $userLanguage->language->icone,
             ];
             $formattedUser['user_language'][] = $formattedUserLanguage;
         }
 
         foreach ($user->user_preference as $userPreference) {
             $formattedUserPreference = [
                 'preference_id' => $userPreference->preference_id,
                 'name' => $userPreference->preference->name,
                 'icone' => $userPreference->preference->icone,
             ];
             $formattedUser['user_preference'][] = $formattedUserPreference;
         }
 
         $formattedUsers[] = $formattedUser;
     }
 
     return response()->json(['users' => $formattedUsers], 200);
 }
 
 /**
     * @OA\Put(
     *     path="/api/users/update_password",
     *     tags={"User"},
     * security={{"bearerAuth": {}}},
     *     summary="Update user password",
     *     description="Update user password.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"old_password", "new_password", "new_password_confirmation"},
     *                 @OA\Property(property="old_password", type="string", example="old_password"),
     *                 @OA\Property(property="new_password", type="string", example="new_password"),
     *                 @OA\Property(property="new_password_confirmation", type="string", example="new_password")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
public function updatePassword(Request $request)
{
    $userId = Auth::id();
    $user = User::find($userId);

    $validator = Validator::make($request->all(), [
        'old_password' => 'required',
        'new_password' => 'required|min:8|confirmed|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
    }

    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json(['message' => 'Old password is incorrect.'], 422);
    }
    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json(['message' => 'Password updated successfully.'], 200);
}



/**
 * @OA\Get(
 *     path="/api/users/travelers",
 *     summary="Obtenir la liste des utilisateurs voyageurs",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des utilisateurs avec le rôle 'traveler'",
 *         @OA\JsonContent(
 *            
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun utilisateur avec le rôle 'traveler' trouvé"
 *     )
 * )
 */
public function getUsersWithRoletraveler()
{
    // Récupérer les utilisateurs qui sont des voyageurs
    $usersWithRole = User::where('is_traveller', true)
        ->where('is_deleted', 0)
        ->with(['user_language.language', 'user_preference.preference'])
        ->get();

    if ($usersWithRole->isEmpty()) {
        return response()->json(['message' => 'Aucun utilisateur voyageur trouvé.'], 404);
    }

    $formattedUsers = [];
    foreach ($usersWithRole as $user) {
        $formattedUser = [
            'id' => $user->id,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'code_pays' => $user->code_pays,
            'email' => $user->email,
            'country' => $user->country,
            'file_profil' => $user->file_profil,
            'city' => $user->city,
            'address' => $user->address,
            'sexe' => $user->sexe,
            'postal_code' => $user->postal_code,
            'is_hote' => $user->is_hote,
            'is_traveller' => $user->is_traveller,
            'is_admin' => $user->is_admin,
            'user_language' => $user->user_language->map(function ($userLanguage) {
                return [
                    'language_id' => $userLanguage->language_id,
                    'name' => $userLanguage->language->name,
                    'icone' => $userLanguage->language->icone,
                ];
            }),
            'user_preference' => $user->user_preference->map(function ($userPreference) {
                return [
                    'preference_id' => $userPreference->preference_id,
                    'name' => $userPreference->preference->name,
                    'icone' => $userPreference->preference->icone,
                ];
            }),
        ];

        $formattedUsers[] = $formattedUser;
    }

    return response()->json(['users' => $formattedUsers], 200);
}

/**
 * @OA\Put(
 *     path="/api/users/update",
 *     summary="Mettre à jour les informations d'un utilisateur",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="nom", type="string"),
 *             @OA\Property(property="prenom", type="string"),
 *             @OA\Property(property="telephone", type="string"),
 *             @OA\Property(property="code_pays", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="pays", type="string"),
 *             @OA\Property(property="ville", type="string"),
 *             @OA\Property(property="addresse", type="string"),
 *             @OA\Property(property="sexe", type="string"),
 *             @OA\Property(property="postal_code", type="string"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Informations de l'utilisateur mises à jour avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Informations de l'utilisateur mises à jour avec succès"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation des données"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Utilisateur non trouvé"
 *     )
 * )
 */
public function updateUser(Request $request)
{
    $userId = Auth::id();
    $validator = Validator::make($request->all(), [
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'telephone' => 'required|string|unique:users,telephone,' . $userId,
        'code_pays' => 'required|string',
        'email' => 'required|email|unique:users,email,' . $userId,
        'pays' => 'required|string',
        'ville' => 'required|string',
        'addresse' => 'required|string',
        'sexe' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }
    
    $user = User::find($userId);
    if (!$user) {
        return response()->json(['error' => 'Utilisateur non trouvé'], 404);
    }

    $user->firstname = strtoupper($request->nom);
    $user->lastname = $request->prenom;
    $user->telephone = $request->telephone;
    $user->code_pays = $request->code_pays;
    $user->email = $request->email;
    $user->country = $request->pays;
    $user->city = $request->ville;
    $user->address = $request->addresse;
    $user->sexe = $request->sexe;
    $user->postal_code = $request->postal_code;
    $user->save();

    return response()->json([
        'message' => 'Informations de l\'utilisateur mises à jour avec succès',
        'user' => $user
    ], 200);
}

/**
 * @OA\Get(
 *     path="/api/users/hotes",
 *     summary="Obtenir la liste des utilisateurs hote",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des utilisateurs ayant le rôle 'hote'",
 *         @OA\JsonContent(
 *            
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun utilisateur avec le rôle 'traveller' trouvé"
 *     )
 * )
 */
public function getUsersWithRoleHost()
{
    $usersWithRole = User::join('commissions', 'users.id', '=', 'commissions.user_id')
        ->where('users.is_hote', true)
        ->where('users.is_deleted', 0)
        ->select('users.*', 'commissions.valeur as commission_value')
        ->with(['user_language.language', 'user_preference.preference'])
        ->get();

    if ($usersWithRole->isEmpty()) {
        return response()->json(['message' => 'Aucun utilisateur hôte non supprimé trouvé.'], 404);
    }

    $formattedUsers = [];
    foreach ($usersWithRole as $user) {
        $formattedUser = [
            'id' => $user->id,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'code_pays' => $user->code_pays,
            'email' => $user->email,
            'country' => $user->country,
            'file_profil' => $user->file_profil,
            'city' => $user->city,
            'address' => $user->address,
            'sexe' => $user->sexe,
            'postal_code' => $user->postal_code,
            'is_hote' => $user->is_hote,
            'is_traveller' => $user->is_traveller,
            'is_admin' => $user->is_admin,
            'commission' =>$user->commission_value,
            'user_language' => $user->user_language->map(function ($userLanguage) {
                return [
                    'language_id' => $userLanguage->language_id,
                    'name' => $userLanguage->language->name,
                    'icone' => $userLanguage->language->icone,
                ];
            }),
            'user_preference' => $user->user_preference->map(function ($userPreference) {
                return [
                    'preference_id' => $userPreference->preference_id,
                    'name' => $userPreference->preference->name,
                    'icone' => $userPreference->preference->icone,
                ];
            }),
            
        ];

        $formattedUsers[] = $formattedUser;
    }

    return response()->json(['users' => $formattedUsers], 200);
}


/**
 * @OA\Get(
 *     path="/api/users/admins",
 *     summary="Obtenir la liste des utilisateurs admin",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des utilisateurs ayant le rôle 'admin'",
 *         @OA\JsonContent(
 *            
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun utilisateur avec le rôle 'admin' trouvé"
 *     )
 * )
 */
public function getUsersWithRoleAdmin()
{

    $usersWithRole = User::where('is_admin', true)
        ->where('is_deleted', 0)
        ->with(['user_language.language', 'user_preference.preference'])
        ->get();

    if ($usersWithRole->isEmpty()) {
        return response()->json(['message' => 'Aucun utilisateur administrateur non supprimé trouvé.'], 404);
    }

    $formattedUsers = [];
    foreach ($usersWithRole as $user) {
        $formattedUser = [
            'id' => $user->id,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'code_pays' => $user->code_pays,
            'email' => $user->email,
            'country' => $user->country,
            'file_profil' => $user->file_profil,
            'city' => $user->city,
            'address' => $user->address,
            'sexe' => $user->sexe,
            'postal_code' => $user->postal_code,
            'is_hote' => $user->is_hote,
            'is_traveller' => $user->is_traveller,
            'is_admin' => $user->is_admin,
            'user_language' => $user->user_language->map(function ($userLanguage) {
                return [
                    'language_id' => $userLanguage->language_id,
                    'name' => $userLanguage->language->name,
                    'icone' => $userLanguage->language->icone,
                ];
            }),
            'user_preference' => $user->user_preference->map(function ($userPreference) {
                return [
                    'preference_id' => $userPreference->preference_id,
                    'name' => $userPreference->preference->name,
                    'icone' => $userPreference->preference->icone,
                ];
            }),
        ];

        $formattedUsers[] = $formattedUser;
    }

    return response()->json(['users' => $formattedUsers], 200);
}



/**
 * @OA\Post(
 *     path="/api/users/login",
 *     summary="make authentification",
 *     tags={"User"},
 * security={{"bearerAuth": {}}},
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="email", type="string", example="a@gmail.com"),
 *             @OA\Property(property="password", type="string", example="P@$$w0rd")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="connected successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="token_type", type="string", example="Bearer"),
 *             @OA\Property(property="role", type="array", @OA\Items(type="string")),
 *             @OA\Property(property="access_token", type="string", example="your-access-token")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     )
 * )
 */

/**
* @OA\Post(
*     path="/api/users/login",
*     summary="make authentification",
*     tags={"User"},
*security={{"bearerAuth": {}}},
*      @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="email", type="string", example ="a@gmail.com"),
   *             @OA\Property(property="password", type="string", example ="P@$$w0rd")
   *         )
   *     ),
*     @OA\Response(
*         response=201,
*         description=" connected successfully"
*     ),
*     @OA\Response(
*         response=401,
*         description="Invalid credentials"
*     )
* )
*/


public function login(Request $request){
    try{
      $request->validate([
        'email' => 'required|email',
        'password' => 'required',
      ]);

      $user = User::where('email', $request->email)->first();
      if($user !=null){
        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'token_type' => 'Bearer',
                'user' => $user,
                'role' => $user->getRoleNames(),
                'access_token' => $token
            ]);
        } else {
            return response()->json(['error' => 'Mot de passe invalide.'], 401);
      }


    }else {
        return response()->json(['error' => 'Adresse email invalide.'], 401);
    }

   } catch(Exception $e) {    
    return response()->json($e);
    }
}

/**
 * @OA\Get(
 *     path="/api/user",
 *     summary="Check authentication status",
 *     description="Check if the user is authenticated and retrieve user data and role",
 *     tags={"Authentication"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="role", type="array", @OA\Items(type="string"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */
public function checkAuth(Request $request){

    try{
        return response()->json([
            'data' => $request->user(),
            'role'=>$request->user()->getRoleNames()
        ]);

    } catch (Exception $e) {
        return response()->json($e->getMessage());
    }
}

}


