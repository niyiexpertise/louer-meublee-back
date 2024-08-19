<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Housing;
use App\Models\HousingSponsoring;
use App\Models\Right;
use App\Models\Sponsoring;
use App\Models\User_right;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HousingSponsoringController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/housingsponsoring/store",
 *     summary="Créer une demande de sponsoring",
 *     description="Permet à un utilisateur de créer une demande de sponsoring pour un logement spécifique.",
 *     tags={"Housing sponsoring"},
 * security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"housing_id", "sponsoring_id", "date_debut"},
 *             @OA\Property(property="housing_id", type="integer", example=1, description="ID du logement pour lequel la demande de sponsoring est faite."),
 *             @OA\Property(property="sponsoring_id", type="integer", example=1, description="ID du tarif de sponsoring choisi."),
 *             @OA\Property(property="date_debut", type="string", format="date", example="2024-09-01", description="Date de début du sponsoring au format YYYY-MM-DD.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Demande de sponsoring créée avec succès.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="data", type="object", description="Données de la réponse"),
 *             @OA\Property(property="message", type="string", example="Demande de sponsoring créée avec succès.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Requête invalide, problème avec les données fournies.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=400),
 *             @OA\Property(property="data", type="object", description="Données de la réponse"),
 *             @OA\Property(property="message", type="string", example="La date de début doit être supérieur à la date d'aujourd'hui.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Le logement ou le tarif de sponsoring n'a pas été trouvé.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="data", type="object", description="Données de la réponse"),
 *             @OA\Property(property="message", type="string", example="Logement non trouvé")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=500),
 *             @OA\Property(property="data", type="object", description="Données de la réponse"),
 *             @OA\Property(property="message", type="string", example="Erreur serveur interne.")
 *         )
 *     )
 * )
 */

    public function store(Request $request)
    {
        try {
            // Validation de la demande
            $request->validate([
                'housing_id' => 'required',
                'sponsoring_id' => 'required',
                'date_debut' => 'required',
            ]);

            $housing = Housing::find($request->housing_id);
            $sponsoring = Sponsoring::find($request->sponsoring_id);

            if (!$housing) {
                return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé');
            }

            if($housing->user_id != Auth::user()->id){
                return (new ServiceController())->apiResponse(404, [], ' Ce logement ne vous appartient pas');
            }

            if (!$sponsoring) {
                return (new ServiceController())->apiResponse(404, [], ' tarif de sponsoring non trouvé');
            }

            $dateDebut = Carbon::parse($request->date_debut);

            if ($dateDebut->lessThanOrEqualTo(Carbon::now())) {
                return (new ServiceController())->apiResponse(404, [], 'La date de début doit être supérieur à la date d\'aujourd\'hui');
            }

            $dateFin = $dateDebut->copy()->addDays($sponsoring->duree);

            $currentMonthStart = $dateDebut->copy()->startOfMonth();
            $currentMonthEnd = $dateDebut->copy()->endOfMonth();
            $activeRequestsCount = HousingSponsoring::where('is_actif', true)
                ->whereBetween('date_debut', [$currentMonthStart, $currentMonthEnd])
                ->count();

            if ($activeRequestsCount >= 40) {
                return (new ServiceController())->apiResponse(404, [], 'Le quota de sponsoring pour ce mois est atteint. Veuillez faire une demande pour un mois où la limite n\'est pas encore atteinte.');
            }

            $exists = HousingSponsoring::where('housing_id',$request->housing_id)->where('sponsoring_id',$request->sponsoring_id)->get();


            foreach($exists as $exist){
                if($exist->is_actif == true && Carbon::parse($request->date_debut)->lessThanOrEqualTo(Carbon::parse($exist->date_fin))){
                    return (new ServiceController())->apiResponse(404, [], 'Votre logement est actuellement sponsorisé');
                }
                if($exist->is_actif == false && Carbon::parse($request->date_debut)->lessThanOrEqualTo(Carbon::parse($exist->date_fin))){

                    // return $exist;
                    return (new ServiceController())->apiResponse(404, [], 'Vous avez déjà déjà fait une demande de sponsoring pour cette période, attendez la validation de l\'admin.');
                }
            }

            $housingSponsoring = new HousingSponsoring();
            $housingSponsoring->housing_id = $request->housing_id;
            $housingSponsoring->sponsoring_id = $request->sponsoring_id;
            $housingSponsoring->date_debut = $dateDebut;
            $housingSponsoring->date_fin = $dateFin;
            $housingSponsoring->is_actif = false;
            $housingSponsoring->save();

            $right = Right::where('name','admin')->first();
                    $adminUsers = User_right::where('right_id', $right->id)->get();
                    foreach ($adminUsers as $adminUser) {

                        $mailadmin = [
                            'title' => "Demande de sponsoring",
                            "body" => "Une demande de sponsoring vient d'être fait par un hôte, veuillez vous connectez pour la valider"
                        ];
                    dispatch( new SendRegistrationEmail($adminUser->user->email, $mailadmin['body'], $mailadmin['title'], 2));
                }

            return (new ServiceController())->apiResponse(200, [], 'Demande de sponsoring créée avec succès');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/hoteSponsoringRequest",
     *     summary="Obtenir les demandes de sponsoring d'un hôte connecté",
     *     tags={"Housing sponsoring"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des demandes de sponsoring de l'hôte connecté",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="duree", type="integer", example=30),
     *                 @OA\Property(property="prix", type="number", format="float", example=100.50),
     *                 @OA\Property(property="description", type="string", example="Description du sponsoring"),
     *                 @OA\Property(property="is_deleted", type="boolean", example=false),
     *                 @OA\Property(property="is_actif", type="boolean", example=true),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erreur de traitement")
     *         ),
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function hoteSponsoringRequest(Request $request)
    {
        try {

            $housingSponsorings = HousingSponsoring::where('is_deleted',false)->get();
            $data = [];

            foreach ($housingSponsorings as $housingSponsoring) {
                if(Housing::whereId($housingSponsoring->housing_id)->first()->user_id == Auth::user()->id){
                    $data[] = [
                        'duree' => Sponsoring::find($housingSponsoring->sponsoring_id)->duree,
                        'prix' => Sponsoring::find($housingSponsoring->sponsoring_id)->prix,
                        'description' => Sponsoring::find($housingSponsoring->sponsoring_id)->description,
                        'Jour de la demande' => Sponsoring::find($housingSponsoring->sponsoring_id)->created_at,
                        'date de commencement du sponsoring'=>  $housingSponsoring->date_debut,
                        'date de fin du sponsoring' =>  $housingSponsoring->date_fin
                    ];
                }
            }

            return (new ServiceController())->apiResponse(200, $data, 'Liste des demandes de sponsoring d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

}
