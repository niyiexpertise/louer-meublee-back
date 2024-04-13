<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\User_role;
use App\Models\User_language;
use App\Models\User_preference;
use App\Models\Preference;
use Validator;
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
     //$user = auth()->user()->id;
    $user_id = 2;

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
                }
            }
        }

        return response()->json(['message' => 'User preferences updated successfully'], 200);
    } catch (QueryException $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
