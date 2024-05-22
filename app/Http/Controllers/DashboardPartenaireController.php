<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Commission;
use App\Models\User;
use Exception;
use Illuminate\Validation\Rule;
use App\Models\user_partenaire;
use Illuminate\Support\Facades\Auth;
class DashboardPartenaireController extends Controller
{
  /**
     * @OA\Get(
     *     path="/api/partenaire/users",
     *     summary="Liste des utilisateurs d'un partenaire connecté",
     *     description="Récupère la liste de tous les utilisateurs qui appartiennent à un partenaire connecté.",
     *     tags={"Dashboard partenaire"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
     *         @OA\JsonContent(
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Partenaire non trouvé.",
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
    public function getUsersForPartenaire(Request $request)
    {
        try {
            $user = auth()->user();
            $userPartenaire = user_partenaire::where('user_id', $user->id)->first();

            if (!$userPartenaire) {
                return response()->json(['error' => 'Partenaire non trouvé.'], 404);
            }

            $users = User::where('partenaire_id', $userPartenaire->id)->get();
            $count = $users->count();

            return response()->json(['count' => $count, 'data' => $users], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
