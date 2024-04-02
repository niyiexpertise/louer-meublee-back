<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\User_role;
use App\Models\User_language;
use App\Models\Review;
use App\Models\Language;
use Validator;
class UserController extends Controller
{
   /**
     * @OA\Get(
     *     path="/api/users/index",
     *     summary="Get all users",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="List of users"
     *
     *     )
     * )
     */
    public function index()
{
    $users = User::with([
        'user_role' => function ($query) {
            $query->select('user_id', 'role_id', 'is_active'); 
            $query->with('role:id,name,icone');
        }, 
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
            'user_role' => [],
            'user_language' => [],
            'user_preference' => [], 
        ];

        foreach ($user->user_role as $userRole) {
            $formattedUserRole = [
                'role_id' => $userRole->role_id,
                'is_active' => $userRole->is_active,
                'name' => $userRole->role->name,
                'icone' => $userRole->role->icone,
            ];
            $formattedUser['user_role'][] = $formattedUserRole;
        }

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
        // Validation des données
        
        
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
            'telephone' => 'required|String|numeric',
            'email' => 'required|email|unique:users',
            'pays' => 'required|string',
            'identity_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ville' => 'required|string',
            'addresse' => 'required|string',
            'sexe' => 'required|string',
            'postal_code' => 'required|string',
            'language_id' => [
                'required',
                'min:1',
                
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
            'lastname' => $request->nom,
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
            
            
        ]);

        $user->save();

        $userRole = new User_role([
            'user_id' => $user->id,
            'role_id' => 2,
        ]);

        $userRole->save();
        $userLanguages = json_decode($request->language_id);
        
        foreach ($userLanguages as $language_id) {
            $userLanguage = new User_language([
                'user_id' => $user->id,
                'language_id' => $language_id,
            ]);

            $userLanguage->save();
        }

        return response()->json(['message' => 'User registered successfully'], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
/**
 * @OA\Delete(
 *   path="/api/users/destroy/{id}",
 *   tags={"User"},
 *   summary="Marquer un utilisateur comme supprimé",
 *   description="Marque un utilisateur comme supprimé en définissant is_deleted à true.",
 *  @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the language",
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
    $user_Id=26;
    //$userId = Auth::id();
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
    $user_Id=26;
    //$userId = Auth::id();
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
        $user_Id=26;
        //$userId = Auth::id();
        $user = User::findOrFail($user_Id);

        $userPreferences = $user->user_preference()->with('preference')->get();

        return response()->json([
            'data' => $userPreferences,
        ]);
    }


}