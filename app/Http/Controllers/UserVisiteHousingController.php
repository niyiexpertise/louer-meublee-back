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

  
    /**
    * @OA\Get(
    *   path="/api/logement/{housing_id}/date/visit_statistics",
    *   tags={"Trafic"},
    *   security={{"bearerAuth": {}}},
    *   summary="Obtenir des statistiques de visites pour un logement à une date donnée",
    *   description="Retourne le nombre total de visites, de visites avec utilisateur, et de revisites pour une date spécifique.",
    *   @OA\Parameter(
    *     name="housing_id",
    *     in="path",
    *     description="ID du logement",
    *     required=true,
    *     @OA\Schema(type="integer")
    *   ),
    *   @OA\Parameter(
    *     name="date",
    *     in="query",
    *     description="Date pour laquelle obtenir les statistiques au format AAAA-MM-JJ",
    *     required=false,
    *     @OA\Schema(type="string", format="date", example="2024-05-04")
    *   ),
    *   @OA\Response(
    *     response=200,
   *     description="Statistiques de visites du logement pour une date donnée",
    *     @OA\JsonContent(
   *       @OA\Property(property="date", type="string", description="Date des statistiques", example="2024-05-04"),
   *       @OA\Property(property="total_visits", type="integer", description="Nombre total de visites"),
   *       @OA\Property(property="total_user_visits", type="integer", description="Nombre total de visites avec utilisateur"),
   *       @OA\Property(property="total_anonymous_visits", type="integer", description="Nombre total de visites sans utilisateur"),
   *       @OA\Property(property="total_user_revisits", type="integer", description="Nombre total de revisites avec utilisateur"),
   *       @OA\Property(property="total_anonymous_revisits", type="integer", description="Nombre total de revisites sans utilisateur")
   *     )
   *   ),
    *   @OA\Response(
   *     response=400,
   *     description="Erreur de requête",
   *     @OA\JsonContent(
   *       @OA\Property(property="message", type="string", example="Erreur lors de la récupération des statistiques.")
   *     )
   *   )
   * )
    */
    public function getVisitStatisticsDate(Request $request, $housing_id)
    {
        $dateParam = $request->input('date', Carbon::now()->toDateString());

        $totalVisits = UserVisiteHousing::where('housing_id', $housing_id)->where('date_de_visite', $dateParam)->count();
        $totalUserVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->where('date_de_visite', $dateParam)
            ->whereNotNull('user_id')
            ->count();
        $totalAnonymousVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->where('date_de_visite', $dateParam)
            ->whereNull('user_id')
            ->count();

        $totalUserRevisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->where('date_de_visite', $dateParam)
            ->whereNotNull('user_id')
            ->sum('revisite_nb');
        $totalAnonymousRevisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->where('date_de_visite', $dateParam)
            ->whereNull('user_id')
            ->sum('revisite_nb');

        return response()->json([
            'date' => $dateParam,
            'total_visits' => $totalVisits,
            'total_user_visits' => $totalUserVisits,
            'total_anonymous_visits' => $totalAnonymousVisits,
            'total_user_revisits' => $totalUserRevisits,
            'total_anonymous_revisits' => $totalAnonymousRevisits,
        ], 200);
    }

    /**
    * @OA\Get(
    *   path="/api/logement/{housing_id}/current_month/visit_statistics",
    *   tags={"Trafic"},
    *   security={{"bearerAuth": {}}},
    *   summary="Obtenir des statistiques de visites pour un logement au mois courant",
    *   description="Retourne les statistiques des visites pour le mois courant, y compris le nombre total de visites, les visites avec utilisateur, les visites anonymes, et les revisites.",
    *   @OA\Parameter(
    *     name="housing_id",
    *     in="path",
   *     description="ID du logement",
    *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Statistiques des visites du mois courant pour un logement",
   *     @OA\JsonContent(
   *       @OA\Property(property="month", type="string", description="Mois des statistiques", example="May 2024"),
   *       @OA\Property(property="total_visits", type="integer", description="Nombre total de visites"),
   *       @OA\Property(property="total_user_visits", type="integer", description="Nombre total de visites avec utilisateur"),
   *       @OA\Property(property="total_anonymous_visits", type="integer", description="Nombre total de visites sans utilisateur"),
   *       @OA\Property(property="total_user_revisits", type="integer", description="Nombre total de revisites avec utilisateur"),
   *       @OA\Property(property="total_anonymous_revisits", type="integer", description="Nombre total de revisites sans utilisateur")
   *     )
   *   ),
   *   @OA\Response(
   *     response=400,
   *     description="Erreur lors de la récupération des statistiques",
   *     @OA\JsonContent(
   *       @OA\Property(property="message", type="string", example="Erreur lors de la récupération des statistiques.")
    *     )
   *   )
    * )
    */
    public function getCurrentMonthVisitStatistics(Request $request, $housing_id)
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $totalVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->count();

        $totalUserVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->whereNotNull('user_id')
            ->count();

        $totalAnonymousVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->whereNull('user_id')
            ->count();

        $totalUserRevisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->whereNotNull('user_id')
            ->sum('revisite_nb');

        $totalAnonymousRevisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->whereNull('user_id')
            ->sum('revisite_nb');

        return response()->json([
            'month' => Carbon::now()->format('F Y'),
            'total_visits' => $totalVisits,
            'total_user_visits' => $totalUserVisits,
            'total_anonymous_visits' => $totalAnonymousVisits,
            'total_user_revisits' => $totalUserRevisits,
            'total_anonymous_revisits' => $totalAnonymousRevisits,
        ], 200);
    }

    /**
    * @OA\Get(
    *   path="/api/logement/{housing_id}/current_year/visit_statistics",
    *   tags={"Trafic"},
    *   security={{"bearerAuth": {}}},
    *   summary="Obtenir des statistiques de visites pour un logement pour l'année actuelle",
    *   description="Retourne le nombre total de visites, les visites avec utilisateur, les visites anonymes, et les revisites pour l'année en cours.",
   *   @OA\Parameter(
   *     name="housing_id",
   *     in="path",
   *     description="ID du logement",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Statistiques des visites pour l'année actuelle",
   *     @OA\JsonContent(
   *       @OA\Property(property="year", type="string", description="Année des statistiques", example="2024"),
   *       @OA\Property(property="total_visits", type="integer", description="Nombre total de visites"),
   *       @OA\Property(property="total_user_visits", type="integer", description="Nombre total de visites avec utilisateur"),
   *       @OA\Property(property="total_anonymous_visits", type="integer", description="Nombre total de visites sans utilisateur"),
   *       @OA\Property(property="total_user_revisits", type="integer", description="Nombre total de revisites avec utilisateur"),
   *       @OA\Property(property="total_anonymous_revisits", type="integer", description="Nombre total de revisites sans utilisateur")
   *     )
   *   ),
   *   @OA\Response(
   *     response=400,
   *     description="Erreur lors de la récupération des statistiques",
   *     @OA\JsonContent(
   *       @OA\Property(property="message", type="string", example="Erreur lors de la récupération des statistiques.")
   *     )
   *   )
   * )
    */
    public function getCurrentYearVisitStatistics(Request $request, $housing_id)
    {
        $startOfYear = Carbon::now()->startOfYear()->toDateString();
        $endOfYear = Carbon::now()->endOfYear()->toDateString();

        $totalVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfYear, $endOfYear])
            ->count();

        $totalUserVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfYear, $endOfYear])
            ->whereNotNull('user_id')
            ->count();

        $totalAnonymousVisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfYear, $endOfYear])
            ->whereNull('user_id')
            ->count();

        $totalUserRevisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfYear, $endOfYear])
            ->whereNotNull('user_id')
            ->sum('revisite_nb');

        $totalAnonymousRevisits = UserVisiteHousing::where('housing_id', $housing_id)
            ->whereBetween('date_de_visite', [$startOfYear, $endOfYear])
            ->whereNull('user_id')
            ->sum('revisite_nb');

        return response()->json([
            'year' => Carbon::now()->format('Y'), 
            'total_visits' => $totalVisits,
            'total_user_visits' => $totalUserVisits,
            'total_anonymous_visits' => $totalAnonymousVisits,
            'total_user_revisits' => $totalUserRevisits,
            'total_anonymous_revisits' => $totalAnonymousRevisits,
        ], 200);
    }

    /**
    * @OA\Get(
    *   path="/api/logement/{housing_id}/yearly/visit_statistics",
    *   tags={"Trafic"},
    *   security={{"bearerAuth": {}}},
   *   summary="Obtenir des statistiques de visites par mois pour un logement d'une année donnée",
   *   description="Retourne les statistiques des visites pour chaque mois d'une année donnée pour un logement spécifique.",
   *   @OA\Parameter(
   *     name="housing_id",
   *     in="path",
   *     description="ID du logement",
   *     required=true,
   *     @OA\Schema(type="integer")
   *   ),
   *   @OA\Parameter(
   *     name="year",
   *     in="query",
   *     description="L'année pour laquelle obtenir les statistiques",
   *     required=false,
   *     @OA\Schema(type="integer", example=2024)
   *   ),
   *   @OA\Response(
   *     response=200,
   *     description="Statistiques des visites par mois pour une année donnée pour un logement",
   *     @OA\JsonContent(
   *       @OA\Property(
   *         property="year",
   *         type="integer",
   *         description="Année des statistiques",
   *         example=2024
   *       ),
   *       @OA\Property(
   *         property="monthly_statistics",
   *         type="array",
   *         description="Statistiques mensuelles",
   *         @OA\Items(
   *           @OA\Property(property="month", type="string", description="Nom du mois", example="January"),
   *           @OA\Property(property="total_visits", type="integer", description="Nombre total de visites"),
   *           @OA\Property(property="total_user_visits", type="integer", description="Nombre total de visites avec utilisateur"),
   *           @OA\Property(property="total_anonymous_visits", type="integer", description="Nombre total de visites sans utilisateur"),
   *           @OA\Property(property="total_user_revisits", type="integer", description="Nombre total de revisites avec utilisateur"),
   *           @OA\Property(property="total_anonymous_revisits", type="integer", description="Nombre total de revisites sans utilisateur")
   *         )
   *       )
   *     )
   *   ),
   *   @OA\Response(
   *     response=400,
   *     description="Erreur lors de la récupération des statistiques",
   *     @OA\JsonContent(
   *       @OA\Property(property="message", type="string", example="Erreur lors de la récupération des statistiques.")
   *     )
   *   )
   * )
    */
    public function getYearlyVisitStatistics(Request $request, $housing_id)
    {
        $year = $request->input('year', Carbon::now()->format('Y'));

        $monthlyStatistics = [];
        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

            $totalVisits = UserVisiteHousing::where('housing_id', $housing_id)
                ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
                ->count();

            $totalUserVisits = UserVisiteHousing::where('housing_id', $housing_id)
                ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
                ->whereNotNull('user_id')
                ->count();

            $totalAnonymousVisits = UserVisiteHousing::where('housing_id', $housing_id)
                ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
                ->whereNull('user_id')
                ->count();

            $totalUserRevisits = UserVisiteHousing::where('housing_id', $housing_id)
                ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
                ->whereNotNull('user_id')
                ->sum('revisite_nb');

            $totalAnonymousRevisits = UserVisiteHousing::where('housing_id', $housing_id)
                ->whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
                ->whereNull('user_id')
                ->sum('revisite_nb');

            $monthlyStatistics[] = [
                'month' => Carbon::createFromDate($year, $month, 1)->format('F'),
                'total_visits' => $totalVisits,
                'total_user_visits' => $totalUserVisits,
                'total_anonymous_visits' => $totalAnonymousVisits,
                'total_user_revisits' => $totalUserRevisits,
                'total_anonymous_revisits' => $totalAnonymousRevisits,
            ];
        }

        return response()->json([
            'year' => $year,
            'monthly_statistics' => $monthlyStatistics,
        ], 200);
    }


}



