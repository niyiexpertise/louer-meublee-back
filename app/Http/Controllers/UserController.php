<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\Charge;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\File;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Right;
use App\Models\User_right;
use App\Models\Equipment;
use App\Models\Review;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use App\Models\Housing_category_file;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as F;
use App\Models\Category;
use App\Models\Housing_charge;
use App\Models\Review_reservation;
use App\Models\Portfeuille;
use App\Models\user_partenaire;
use App\Models\Portfeuille_transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationLoginEmail;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

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
            'is_deleted' => $user->is_deleted,
            'is_blocked' => $user->is_blocked,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'solde_portfeuille' => $user->portfeuille->solde,
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

    return response()->json(['data' => $formattedUsers], 200);
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

    $user_Id = Auth::id();
    $reviews = Review::where('user_id', $user_Id)->get();

    return response()->json([
        'data' => $reviews
    ]);
}

/**
 * @OA\Get(
 *     path="/api/users/userLanguages",
 *     tags={"User"},
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
    $user_Id = Auth::id();
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
         'profile_photo' => 'required',
     ]);

     if ($validator->fails()) {
         return response()->json(['error' => $validator->errors()], 400);
     }
     $oldProfilePhotoUrl = $user->file_profil;

     if ($oldProfilePhotoUrl) {
         $parsedUrl = parse_url($oldProfilePhotoUrl);
         $oldProfilePhotoPath = public_path($parsedUrl['path']);
         if (F::exists($oldProfilePhotoPath)) {
             F::delete($oldProfilePhotoPath);
         }
     }

     $identity_profil_url = '';

     $images = $request->file('profile_photo');
     if(!isset($images[0])){
         return (new ServiceController())->apiResponse(404, [], 'L\'image n\'a  pas été correctement envoyé.');
     }
     $image =$images[0];

     $identity_profil_url = $this->fileService->uploadFiles($image, 'image/photo_profil', 'extensionImage');
     if ($identity_profil_url['fails']) {
        return (new ServiceController())->apiResponse(404, [], $identity_profil_url['result']);
    }
     $user->file_profil = $identity_profil_url['result'];
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
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User non trouvé.'], 404);
        }
        $user->is_blocked = true;
        $user->save();
        return response()->json(['data' => 'This user is block successfuly.'], 200);
    } catch(Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
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
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User non trouvé.'], 404);
        }
        $user->is_blocked = false;
        $user->save();
        return response()->json(['data' => 'User débloqué avec succès.'], 200);
    }catch (Exception $e){
          return response()->json(['error' => $e->getMessage()], 500);
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
             'solde_portfeuille' => $user->portfeuille->solde,
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
        return (new ServiceController())->apiResponse(404,$validator->errors(),'The given data was invalid.');
    }

    if (!Hash::check($request->old_password, $user->password)) {
        return (new ServiceController())->apiResponse(404,[],'Old password is incorrect.');
    }
    $user->password = Hash::make($request->new_password);
    $user->save();

    return (new ServiceController())->apiResponse(200,[],'Password updated successfully.');
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

    $travelerRole = DB::table('rights')->where('name', 'traveler')->first();

    if (!$travelerRole) {
        return response()->json(['message' => 'Le rôle de traveler n\'a pas été trouvé.'], 404);
    }

    $usersWithRole = User::whereHas('user_right', function ($query) use ($travelerRole) {
        $query->where('right_id', $travelerRole->id);
    })
    ->where('is_deleted', 0)
    ->with(['user_language.language', 'user_preference.preference'])
    ->with('portfeuille')
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
            'solde_portfeuille' => $user->portfeuille->solde,  // Solde du portefeuille
            'user_language' => $user->user_language->map(function ($userLanguage) {
                return [
                    'language_id' => $userLanguage->language_id,
                    'name' => $userLanguage->name,
                    'icone' => $userLanguage->icone,
                ];
            }),
            'user_preference' => $user->user_preference->map(function ($userPreference) {
                return [
                    'preference_id' => $userPreference->preference_id,
                    'name' => $userPreference->name,
                    'icone' => $userPreference->icone,
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
        'lastname' => 'required|string',
        'firstname' => 'required|string',
        'telephone' => 'required|string|unique:users,telephone,' . $userId,
        'code_pays' => 'required|string',
        'email' => 'required|email|unique:users,email,' . $userId,
        'country' => 'required|string',
        'city' => 'required|string',
        'address' => 'required|string',
        'sexe' => 'required|string',
    ]);

    if ($validator->fails()) {
        return (new ServiceController())->apiResponse(404,[],$validator->errors());
    }

    $user = User::find($userId);
    if (!$user) {
        return (new ServiceController())->apiResponse(404,[], 'Utilisateur non trouvé');
    }

    $user->lastname = $request->lastname;
    $user->firstname = strtoupper($request->firstname);
    $user->telephone = $request->telephone;
    $user->code_pays = $request->code_pays;
    $user->email = $request->email;
    $user->country = $request->country;
    $user->city = $request->city;
    $user->address = $request->address;
    $user->sexe = $request->sexe;
    $user->postal_code = $request->postal_code;
    $user->save();

    return (new ServiceController())->apiResponse(200,$user,'Informations de l\'utilisateur mises à jour avec succès');

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

    $hostRole = Right::where('name', 'hote')->first();

    if (!$hostRole) {
        return response()->json(['message' => 'Le rôle d\'hôte n\'a pas été trouvé.']);
    }

    $usersWithRole = User::whereHas('user_right', function ($query) use ($hostRole) {
        $query->where('right_id', $hostRole->id);
    })
    ->where('is_deleted', 0)
    ->with(['user_language.language', 'user_preference.preference'])
    ->with('portfeuille')
    ->leftJoin('commissions', 'users.id', '=', 'commissions.user_id')
    ->select('users.*', 'commissions.valeur as commission_value')
    ->get();

    if ($usersWithRole->isEmpty()) {
        return response()->json(['message' => 'Aucun utilisateur hôte non supprimé trouvé.']);
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
            'solde_portfeuille' => $user->portfeuille->solde,
            'commission' => $user->commission_value,
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

    return response()->json(['users' => $formattedUsers]);

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
    // Obtenez l'ID du rôle 'admin'
    $adminRole = DB::table('rights')->where('name', 'admin')->first();

    if (!$adminRole) {
        return response()->json(['message' => 'Le rôle d\'admin n\'a pas été trouvé.'], 404);
    }

    $usersWithRole = User::whereHas('user_right', function ($query) use ($adminRole) {
        $query->where('right_id', $adminRole->id);
    })
    ->where('is_deleted', 0)
    ->with(['user_language.language', 'user_preference.preference'])
    ->with('portfeuille')
    ->get();

    if ($usersWithRole->isEmpty()) {
        return response()->json(['message' => 'Aucun utilisateur admin trouvé.'], 404);
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
            'solde_portfeuille' => $user->portfeuille->solde,  // Solde du portefeuille
            'user_language' => $user->user_language->map(function ($userLanguage) {
                return [
                    'language_id' => $userLanguage->language->id,
                    'name' => $userLanguage->language->name,
                    'icone' => $userLanguage->language->icone,
                ];
            }),
            'user_preference' => $user->user_preference->map(function ($userPreference) {
                return [
                    'preference_id' => $userPreference->preference_id,
                    'name' => $userPreference->name,
                    'icone' => $userPreference->icone,
                ];
            }),
        ];

        $formattedUsers[] = $formattedUser;
    }

    return response()->json(['users' => $formattedUsers], 200);
}




/**
 * @OA\Get(
 *     path="/api/users/detail/{userId}",
 *     summary="Obtenir les détails d'un utilisateur",
 *     security={{"bearerAuth": {}}},
 *     description="Retourne des détails complets pour un utilisateur spécifique, y compris les informations personnelles, les langues, les préférences, le nombre de logements, le nombre de réservations, le solde du portefeuille, et le nombre total de transactions.",
 *     tags={"User"},
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         description="ID de l'utilisateur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Détails de l'utilisateur obtenus avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="user_info",
 *                     type="object",
 *                     description="Informations sur l'utilisateur",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="lastname", type="string"),
 *                     @OA\Property(property="firstname", type="string"),
 *                     @OA\Property(property="email", type="string"),
 *                     @OA\Property(property="code_pays", type="string"),
 *                     @OA\Property(property="telephone", type="string"),
 *                     @OA\Property(property="country", type="string"),
 *                     @OA\Property(property="file_profil", type="string", nullable=true),
 *                     @OA\Property(property="city", type="string"),
 *                     @OA\Property(property="address", type="string"),
 *                     @OA\Property(property="sexe", type="string"),
 *                     @OA\Property(property="postal_code", type="string", nullable=true),
 *                     @OA\Property(property="is_hote", type="boolean"),
 *                     @OA\Property(property="is_traveller", type="boolean"),
 *                     @OA\Property(property="is_admin", type="boolean"),
 *                 ),
 *                 @OA\Property(
 *                     property="languages",
 *                     type="array",
 *                     description="Langues préférées de l'utilisateur",
 *                     @OA\Items(
 *                         @OA\Property(property="language_id", type="integer"),
 *                         @OA\Property(property="language_name", type="string")
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="preferences",
 *                     type="array",
 *                     description="Préférences de l'utilisateur",
 *                     @OA\Items(
 *                         @OA\Property(property="preference_id", type="integer"),
 *                         @OA\Property(property="preference_name", type="string")
 *                     )
 *                 ),
 *                 @OA\Property(property="total_housings", type="integer", description="Nombre de logements"),
 *                 @OA\Property(property="total_reservations", type="integer", description="Nombre de réservations"),
 *                 @OA\Property(property="solde", type="number", format="float", description="Solde du portefeuille"),
 *                 @OA\Property(property="total_transactions", type="integer", description="Nombre total de transactions")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Utilisateur non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Utilisateur non trouvé.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string"),
 *             @OA\Property(property="message", type="string", example="Une erreur s'est produite.")
 *         )
 *     )
 * )
 */

public function getUserDetails($userId) {

    $user = User::find($userId);

    if (!$user) {
        return response()->json([
            'message' => 'Utilisateur non trouvé.'
        ], 404);
    }

    $languages = $user->user_language->map(function($userLanguage) {
        return [
            'language_id' => $userLanguage->language_id,
            'language_name' => $userLanguage->language->name,
            'language_icone' => $userLanguage->language->icone
        ];
    });

    $preferences = $user->user_preference->map(function($userPreference) {
        return [
            'preference_id' => $userPreference->preference_id,
            'preference_name' => $userPreference->preference->name,
            'preference_icone' => $userPreference->preference->icone
        ];
    });

    $total_housings = Housing::where('user_id', $userId)->count();
    $total_reservations = Reservation::where('user_id', $userId)->count();

    $portefeuille = Portfeuille::where('user_id', $userId)->first();
    $solde = $portefeuille ? $portefeuille->solde : 0;    $total_transactions = Portfeuille_transaction::where('portfeuille_id', $portefeuille->id)->count();

    $user_details = [
        'user_info' => [
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
        ],
        'languages' => $languages,
        'preferences' => $preferences,
        'total_housings' => $total_housings,
        'total_reservations' => $total_reservations,
        'solde' => $solde,
        'total_transactions' => $total_transactions,
        'has_partenaire' => is_null($user->partenaire_id)?false:true,
    ];

    return response()->json([
        'data' => $user_details
    ], 200);
}
/**
     * @OA\Get(
     *     path="/api/users/getUserReservationCount",
     *     summary="Récupérer le nombre de réservations de l'utilisateur connecté,etc.en gros il s'agit des informations utiles avant de faire une reservation que vous devrez recuperer sur l'utilisateur connecté.",
     *     description="Récupère le nombre de réservations effectuées par l'utilisateur actuellement connecté.",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Nombre de réservations récupéré avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="count", type="integer", description="Nombre de réservations")
     *             }
     *         ),
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
    public function getUserReservationCount(Request $request)
    {
        try {
            $user = auth()->user();
            $userId = $user->id;

            $reservationCount = Reservation::where('user_id', $userId)->where('valeur_reduction_code_promo','!=', 0)->count();

            $userPartenaire = user_partenaire::where('id', $user->partenaire_id)->first();
            $codePromoDetails = $userPartenaire ? [
                'id'=> $userPartenaire->id,
                'code_promo' => $userPartenaire->code_promo,
                'reduction_traveler' => $userPartenaire->reduction_traveler,
                'number_of_reservation' => $userPartenaire->number_of_reservation,
            ] : null;

            if($userPartenaire){
                $nombre_reservation_restante = intval($userPartenaire->number_of_reservation - $reservationCount);
            }

            $nombre_reservation_restante = $nombre_reservation_restante ??null;

            return response()->json([
                'count_reservation_with_promo_inscription' => ($nombre_reservation_restante>0)?$nombre_reservation_restante:0,
                'code_promo' => $codePromoDetails
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' =>$e->getMessage()], 500);
        }
    }


}


