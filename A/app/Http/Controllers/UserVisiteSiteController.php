<?php

namespace App\Http\Controllers;

use App\Models\UserVisite_Site;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserVisiteSiteController extends Controller
{

    public function recordSiteVisit($userId=null)
    {
        $date = Carbon::now()->toDateString();
        
        $time = Carbon::now()->toTimeString();

        $existingVisit = UserVisite_Site::where('date_de_visite', $date)
            ->where(function ($query) use ($userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->whereNull('user_id');
                }
            })
            ->first();

        if ($existingVisit) {
            $existingVisit->revisite_nb += 1;
            $existingVisit->heure = $time;
            $existingVisit->save();
        } else {

            $visit = new UserVisite_Site([
                'user_id' => $userId,
                'revisite_nb' => 0,
                'date_de_visite' => $date,
                'heure' => $time,
            ]);
            $visit->save();
        }

        return response()->json([
            'message' => 'Visite enregistrée avec succès.',
            'visit' => $existingVisit ?? $visit,
        ], 200);
    }

    /**
    * @OA\Get(
        *     path="/api/site/visit_statistics",
        *     tags={"Trafic"},
       * security={{"bearerAuth": {}}},
        *     summary="Obtenir des statistiques sur les visites du site",
        *     description="Retourne des statistiques sur le nombre total de visites, les visites avec utilisateur, et les revisites.",
        * security={{"bearerAuth": {}}},
        *     @OA\Response(
        *         response=200,
        *         description="Statistiques de visites du site",
        *         @OA\JsonContent(
        *             @OA\Property(property="total_visits", type="integer", description="Nombre total de visites"),
        *             @OA\Property(property="total_user_visits", type="integer", description="Nombre total de visites avec utilisateur"),
        *             @OA\Property(property="total_anonymous_visits", type="integer", description="Nombre total de visites sans utilisateur"),
        *             @OA\Property(property="total_user_revisits", type="integer", description="Nombre total de revisites avec utilisateur"),
        *             @OA\Property(property="total_anonymous_revisits", type="integer", description="Nombre total de revisites sans utilisateur")
        *         )
        *     ),
        *     @OA\Response(
        *         response=400,
        *         description="Erreur de requête",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération des statistiques.")
        *         )
        *     )
        * )
        * 
        */

    public function getSiteVisitStatistics()
    {
        $totalVisits = UserVisite_Site::count();
        $totalUserVisits = UserVisite_Site::whereNotNull('user_id')->count();
        $totalAnonymousVisits = UserVisite_Site::whereNull('user_id')->count();

        $totalUserRevisits = UserVisite_Site::whereNotNull('user_id')->sum('revisite_nb');
        $totalAnonymousRevisits = UserVisite_Site::whereNull('user_id')->sum('revisite_nb');

        return response()->json([
            'total_visits' => $totalVisits,
            'total_user_visits' => $totalUserVisits,
            'total_anonymous_visits' => $totalAnonymousVisits,
            'total_user_revisits' => $totalUserRevisits,
            'total_anonymous_revisits' => $totalAnonymousRevisits,
        ], 200);
    }

    /**

    * @OA\Get(
        *   path="/api/site/date/visit_statistics",
        *   tags={"Trafic"},
        *   summary="Obtenir des statistiques de visites pour une date donnée",
        *   description="Retourne le nombre total de visites, de visites avec utilisateur, et de revisites pour une date spécifique.",
           * security={{"bearerAuth": {}}},
        *   @OA\Parameter(
        *     name="date",
        *     in="query",
        *     description="Date pour laquelle obtenir les statistiques au format AAAA-MM-JJ",
        *     required=false,
        *     @OA\Schema(type="string", format="date", example="2024-05-04")
        *   ),
        *   @OA\Response(
        *     response=200,
        *     description="Statistiques de visites du site pour une date donnée",
        *     @OA\JsonContent(
        *       @OA\Property(property="date", type="string", description="Date des statistiques", example="2024-05-04"),
        *       @OA\Property(property="total_visits", type="integer", description="Nombre total de visites"),
        *       @OA\Property(property="total_user_visits", type="integer", description="Nombre total de visites avec utilisateur"),
        *       @OA\Property(property="total_anonymous_visits", type="integer", description="Nombre total de visites sans utilisateur"),
        *       @OA\Property(property="total_user_revisits", type="integer", description="Nombre total de revisites avec utilisateur"),
        *       @OA\Property(property="total_anonymous_revisits", type="integer", description="Nombre total de revisites sans utilisateur"),
        *     )
        *   ),
        *   @OA\Response(
        *     response=400,
        *     description="Erreur de requête",
        *     @OA\JsonContent(
        *       @OA\Property(property="message", type="string", example="Date invalide ou paramètre manquant.")
        *     )
        *   )
        * )
        */

    public function getSiteVisitStatisticsDate(Request $request)
{
    $dateParam = $request->input('date', Carbon::now()->toDateString());

    $totalVisits = UserVisite_Site::where('date_de_visite', $dateParam)->count();
    $totalUserVisits = UserVisite_Site::where('date_de_visite', $dateParam)
        ->whereNotNull('user_id')
        ->count();
    $totalAnonymousVisits = UserVisite_Site::where('date_de_visite', $dateParam)
        ->whereNull('user_id')
        ->count();

    $totalUserRevisits = UserVisite_Site::where('date_de_visite', $dateParam)
        ->whereNotNull('user_id')
        ->sum('revisite_nb');
    $totalAnonymousRevisits = UserVisite_Site::where('date_de_visite', $dateParam)
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
 *   path="/api/site/current_month/visit_statistics",
 *   tags={"Trafic"},
 *   summary="Obtenir des statistiques de visites pour le mois courant",
 *   description="Retourne les statistiques des visites pour le mois courant, y compris le nombre total de visites, les visites avec utilisateur, les visites anonymes, et les revisites.",
    * security={{"bearerAuth": {}}},
 *   @OA\Response(
 *     response=200,
 *     description="Statistiques des visites du mois courant",
 *     @OA\JsonContent(
 *       @OA\Property(property="month", type="string", description="Mois de statistiques", example="May 2024"),
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
public function getCurrentMonthVisitStatistics()
{
    $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
    $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

    $totalVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])->count();
    $totalUserVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
        ->whereNotNull('user_id')
        ->count();
    $totalAnonymousVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
        ->whereNull('user_id')
        ->count();

    $totalUserRevisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
        ->whereNotNull('user_id')
        ->sum('revisite_nb');
    $totalAnonymousRevisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
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
 *   path="/api/site/current_year/visit_statistics",
 *   tags={"Trafic"},
 *   summary="Obtenir des statistiques de visites pour l'année actuelle",
 *   description="Retourne le nombre total de visites, les visites avec utilisateur, les visites anonymes, et les revisites pour l'année en cours.",
    * security={{"bearerAuth": {}}},
 *   @OA\Response(
 *     response=200,
 *     description="Statistiques des visites pour l'année actuelle",
 *     @OA\JsonContent(
 *       @OA\Property(property="year", type="string", description="Année de statistiques", example="2024"),
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

public function getCurrentYearVisitStatistics()
{
    // Obtenir le début et la fin de l'année actuelle
    $startOfYear = Carbon::now()->startOfYear()->toDateString();
    $endOfYear = Carbon::now()->endOfYear()->toDateString();

    $totalVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfYear, $endOfYear])->count();
    $totalUserVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfYear, $endOfYear])
        ->whereNotNull('user_id')
        ->count();
    $totalAnonymousVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfYear, $endOfYear])
        ->whereNull('user_id')
        ->count();

    $totalUserRevisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfYear, $endOfYear])
        ->whereNotNull('user_id')
        ->sum('revisite_nb');
    $totalAnonymousRevisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfYear, $endOfYear])
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
 *   path="/api/site/yearly/visit_statistics",
 *   tags={"Trafic"},
 *   summary="Obtenir des statistiques de visites par mois pour une année donnée",
 *   description="Retourne les statistiques des visites pour chaque mois d'une année donnée.",
 *    security={{"bearerAuth": {}}},
 *   @OA\Parameter(
 *     name="year",
 *     in="query",
 *     description="L'année pour laquelle obtenir les statistiques",
 *     required=false,
 *     @OA\Schema(type="integer", example=2024)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Statistiques des visites par mois pour une année donnée",
 *     @OA\JsonContent(
 *       @OA\Property(property="year", type="integer", description="Année des statistiques", example=2024),
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
public function getYearlyVisitStatistics(Request $request)
{

    $year = $request->input('year', Carbon::now()->format('Y'));

    $monthlyStatistics = [];
    for ($month = 1; $month <= 12; $month++) {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $totalVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])->count();
        $totalUserVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->whereNotNull('user_id')
            ->count();
        $totalAnonymousVisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->whereNull('user_id')
            ->count();

        $totalUserRevisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
            ->whereNotNull('user_id')
            ->sum('revisite_nb');
        $totalAnonymousRevisits = UserVisite_Site::whereBetween('date_de_visite', [$startOfMonth, $endOfMonth])
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
