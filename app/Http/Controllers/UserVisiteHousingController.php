<?php

namespace App\Http\Controllers;

use App\Models\UserVisiteHousing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserVisiteHousingController extends Controller
{
    public function recordHousingVisit($housingId, $userId = null)
{
    $currentDate = Carbon::now()->toDateString();
    $currentTime = Carbon::now()->toTimeString();

    $existingVisit = UserVisiteHousing::where('housing_id', $housingId)
        ->where('date_de_visite', $currentDate)
        ->where(function($query) use ($userId) {
            if (is_null($userId)) {
                $query->whereNull('user_id'); 
            } else {
                $query->where('user_id', $userId); 
            }
        })
        ->first();

    if ($existingVisit) {
        $existingVisit->revisite_nb += 1;
        $existingVisit->heure = $currentTime;
        $existingVisit->save();

        return response()->json([
            'message' => 'Visite mise à jour avec succès.',
            'visit' => $existingVisit,
        ], 200);
    } else {
        $visit = new UserVisiteHousing([
            'housing_id' => $housingId,
            'date_de_visite' => $currentDate,
            'heure' => $currentTime,
            
        ]);

        if ($userId) {
            $visit->user_id = $userId;
        }
        $visit->revisite_nb = 0;
        $visit->save();

        return response()->json([
            'message' => 'Visite enregistrée avec succès.',
            'visit' => $visit,
        ], 200);
    }
}



 /**
     * @OA\Get(
     *     path="/api/logement/{housingId}/visit_statistics",
     *     summary="Obtenir des statistiques sur les visites pour un logement donné",
     *     description="Retourne le nombre total de visites, le nombre total de visites avec user_id, 
     *         le nombre total de revisites avec user_id, et le nombre total de visites sans user_id.",
     *     operationId="getHousingVisitStatistics",
     *    security={{"bearerAuth": {}}},
     *     tags={"Trafic"},
     *     @OA\Parameter(
     *         name="housingId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du logement"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques des visites pour le logement",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_visits", type="integer", description="Nombre total de visites"),
     *             @OA\Property(property="total_user_visits", type="integer", description="Nombre total de visites avec user_id"),
     *             @OA\Property(property="total_user_revisits", type="integer", description="Nombre total de revisites avec user_id"),
     *             @OA\Property(property="total_anonymous_visits", type="integer", description="Nombre total de visites sans user_id"),
     *             @OA\Property(property="total_anonymous_revisits", type="integer", description="Nombre total de revisites sans user_id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Logement non trouvé"
     *     )
     * )
     */
    public function getHousingVisitStatistics($housingId)
  {

    $totalVisits = UserVisiteHousing::where('housing_id', $housingId)->count();

    $totalUserVisits = UserVisiteHousing::where('housing_id', $housingId)
        ->whereNotNull('user_id')
        ->count();

    $totalUserRevisits = UserVisiteHousing::where('housing_id', $housingId)
        ->whereNotNull('user_id')
        ->sum('revisite_nb');

    $totalAnonymousVisits = UserVisiteHousing::where('housing_id', $housingId)
        ->whereNull('user_id')
        ->count();

    $totalAnonymousRevisits = UserVisiteHousing::where('housing_id', $housingId)
        ->whereNull('user_id')
        ->sum('revisite_nb');

    return response()->json([
        'total_user_visits' => $totalUserVisits,
        'total_user_revisits' => $totalUserRevisits,
        'total_anonymous_visits' => $totalAnonymousVisits,
        'total_anonymous_revisits' => $totalAnonymousRevisits,
    ], 200);
  }


}



