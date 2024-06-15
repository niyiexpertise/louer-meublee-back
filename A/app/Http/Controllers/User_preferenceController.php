<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\User_role;
use App\Models\User_language;
use Illuminate\Support\Facades\Auth;
use App\Models\User_preference;
use App\Models\Preference;
use Validator;
use Illuminate\Validation\Rule;
class User_preferenceController extends Controller
{
/**
 * @OA\Post(
 *   path="/api/users/preference/add",
 *   tags={"User_preference"},
 * security={{"bearerAuth": {}}},
 *   summary="Ajouter les préférences d'un utilisateur",
 *   description="Ajoute les préférences sélectionnées par un utilisateur.",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="preferences",
 *           type="array",
 *           @OA\Items(type="integer"),
 *           description="Liste des identifiants de préférence à ajouter"
 *         ),
 *         required={"preferences"}
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Les préférences de l'utilisateur ont été mises à jour avec succès")
 *     )
 *   ),
 *   @OA\Response(
 *     response=400,
 *     description="Erreur de validation",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="string")
 *     )
 *   ),
 *   @OA\Response(
 *     response=500,
 *     description="Erreur interne du serveur",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="string")
 *     )
 *   )
 * )
 */
public function AddUserPreferences(Request $request)
{
    $request->validate([
        'preferences' => 'required|array',
    ]);

    $user_id = auth()->user()->id;
    $preferencesAlreadyExist = [];
    $preferencesAdded = [];

    try {
        foreach ($request->preferences as $preferenceId) {
            $preferenceExists = Preference::where('id', $preferenceId)->exists();

            if ($preferenceExists) {
                $existingPreference = User_preference::where('user_id', $user_id)
                    ->where('preference_id', $preferenceId)
                    ->exists();

                if (!$existingPreference) {
                    $userPreference = new User_preference();
                    $userPreference->user_id = $user_id;
                    $userPreference->preference_id = $preferenceId;
                    $userPreference->save();
                    $preferencesAdded[] = $preferenceId;
                } else {
                    $preferencesAlreadyExist[] = $preferenceId;
                }
            }
        }

        if (empty($preferencesAdded)) {
            return response()->json(['message' => 'Aucune préférence ajoutée. Toutes les préférences avaient été déjà ajoutée.'], 200);
        }

        if (!empty($preferencesAlreadyExist)) {
            $preferenceNames = Preference::whereIn('id', $preferencesAlreadyExist)->pluck('name')->toArray();
            $message = 'Préférences ajoutées avec succès, mais les suivantes existent déjà et n\'ont plus été ajouté: ' . implode(", ", $preferenceNames);
            return response()->json(['message' => $message], 200);
        }

        return response()->json(['message' => 'Préférences ajoutées avec succès.'], 200);

    } catch (QueryException $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


/**
 * @OA\Post(
 *   path="/api/users/preference/remove",
 *   tags={"User_preference"},
 * security={{"bearerAuth": {}}},
 *   summary="Supprimer les préférences d'un utilisateur",
 *   description="Supprimer les préférences sélectionnées par un utilisateur.",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *           property="preferences",
 *           type="array",
 *           @OA\Items(type="integer"),
 *           description="Liste des identifiants de préférence à supprimer"
 *         ),
 *         required={"preferences"}
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Succès",
 *     @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Les préférences de l'utilisateur ont été mises à jour avec succès")
 *     )
 *   ),
 *   @OA\Response(
 *     response=400,
 *     description="Erreur de validation",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="string")
 *     )
 *   ),
 *   @OA\Response(
 *     response=500,
 *     description="Erreur interne du serveur",
 *     @OA\JsonContent(
 *       @OA\Property(property="error", type="string")
 *     )
 *   )
 * )
 */


        public function RemoveUserPreferences(Request $request)
        {
            $request->validate([
                'preferences' => 'required|array',
            ]);

            $user_id = auth()->user()->id;
            $preferencesNotFound = [];
            $preferencesRemoved = [];

            try {
                foreach ($request->preferences as $preferenceId) {
                    $userPreference = User_preference::where('user_id', $user_id)
                        ->where('preference_id', $preferenceId)
                        ->first();

                    if ($userPreference) {
                        $userPreference->delete();
                        $preferencesRemoved[] = $preferenceId;
                    } else {
                        $preferencesNotFound[] = $preferenceId;
                    }
                }

                if (empty($preferencesRemoved)) {
                    return response()->json(['message' => 'Aucune préférence supprimée. Aucune des préférences spécifiées n\'existait pour l\'utilisateur.'], 200);
                }

                if (!empty($preferencesNotFound)) {
                    $preferenceNames = Preference::whereIn('id', $preferencesNotFound)->pluck('name')->toArray();
                    $message = 'Préférences supprimées avec succès, mais les suivantes n\'étaient pas trouvées pour cet utilisateur: ' . implode(", ", $preferenceNames);
                    return response()->json(['message' => $message], 200);
                }

                return response()->json(['message' => 'Préférences supprimées avec succès.'], 200);

            } catch (QueryException $e) {
                return response()->json(['error' => 'Erreur lors de la suppression des préférences: ' . $e->getMessage()], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Une erreur inattendue s\'est produite: ' . $e->getMessage()], 500);
            }
        }


                /**
         * @OA\Get(
         *     path="/api/users/preference/show",
         *     tags={"User_preference"},
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
            $user = User::findOrFail($userId);

            $userPreferences = $user->user_preference()->with('preference')->get();

            return response()->json([
                'data' => $userPreferences,
            ]);
        }



}
