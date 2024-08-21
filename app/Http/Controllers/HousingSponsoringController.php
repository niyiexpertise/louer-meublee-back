<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Housing;
use App\Models\HousingSponsoring;
use App\Models\Payement;
use App\Models\Portfeuille;
use App\Models\Portfeuille_transaction;
use App\Models\Right;
use App\Models\Sponsoring;
use App\Models\User_right;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
 *            
 *             @OA\Property(property="housing_id", type="integer", example=1, description="ID du logement pour lequel la demande de sponsoring est faite."),
 *             @OA\Property(property="sponsoring_id", type="integer", example=1, description="ID du tarif de sponsoring choisi."),
 *             @OA\Property(property="date_debut", type="string", format="date", example="2024-09-01", description="Date de début du sponsoring au format YYYY-MM-DD."),
 *              @OA\Property(property="nombre", type="integer", example=1, description="nombre de fois que vous voulez bénéficier du tarif."),
 *              @OA\Property(property="payment_method", type="string", example="portfeuille", description="nombre de fois que vous voulez bénéficier du tarif."),
 *               @OA\Property(property="id_transaction", type="string", example="portfeuille", description="id de la transaction."),
 *               @OA\Property(property="statut_paiement", type="string", example="portfeuille", description="Statut du paiement."),
 *               @OA\Property(property="montant", type="integer", example=5, description="montant."),
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

            $request->validate([
                'housing_id' => 'required',
                'sponsoring_id' => 'required',
                'date_debut' => 'required',
                'nombre',
                'payment_method' => 'required|string',
                'id_transaction' => 'required|string',
                'statut_paiement' => 'required',
                'montant' => 'nullable|numeric'
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

            $nombre = $request->nombre??1;

            if($request->nombre){

                if(!is_int($request->nombre)){
                    return (new ServiceController())->apiResponse(404, [], ' Le nombre de fois dont vous souhaité bénéficié du tarif doit être un entier');
                }

                if($request->nombre<=0){
                    return (new ServiceController())->apiResponse(404, [], ' Le nombre de fois dont vous souhaité bénéficié du tarif doit être supérieur à 0');
                }
            }

            if(!is_numeric($request->montant)){
                return (new ServiceController())->apiResponse(404, [], 'Le montant doit être un entier');
            }

            if($request->montant<=0){
                return (new ServiceController())->apiResponse(404, [], 'Le montant doit être supérieur à 0 ');
            }

            $dateDebut = Carbon::parse($request->date_debut);

            if ($dateDebut->lessThanOrEqualTo(Carbon::now())) {
                return (new ServiceController())->apiResponse(404, [], 'La date de début doit être supérieur à la date d\'aujourd\'hui');
            }

            $duree= $nombre * $sponsoring->duree;
            $prix_tarif = $nombre * $sponsoring->prix;

            $dateFin = $dateDebut->copy()->addDays($duree);

            $exists = HousingSponsoring::where('housing_id',$request->housing_id)->where('sponsoring_id',$request->sponsoring_id)->get();


            foreach($exists as $exist){
                if($exist->is_actif == true && Carbon::parse($request->date_debut)->lessThanOrEqualTo(Carbon::parse($exist->date_fin))){
                    return (new ServiceController())->apiResponse(404, [], 'Votre logement est actuellement sponsorisé');
                }
                if($exist->is_actif == false && Carbon::parse($request->date_debut)->lessThanOrEqualTo(Carbon::parse($exist->date_fin))){

                    return (new ServiceController())->apiResponse(404, [], 'Vous avez déjà déjà fait une demande de sponsoring pour cette période, attendez la validation de l\'admin.');
                }
            }
            DB::beginTransaction();

            if($request->payment_method =='portfeuille'){
                $userPostefeuille = Portfeuille::where('user_id',Auth::user()->id)->first();

                if($userPostefeuille->solde < $prix_tarif){
                    return (new ServiceController())->apiResponse(404, [], 'Solde insuffisant. Veuillez recharger votre portefeuille.');
                };

            }else{

                if(!$request->montant){
                    return (new ServiceController())->apiResponse(404, [], 'Veuillez renseigner le montant du paiement');
                }

                if($request->montant < $prix_tarif){
                    return (new ServiceController())->apiResponse(404, [], "Le montant est insuffisant. Vous devez payer $prix_tarif XOF");
                }

            }

            $housingSponsoring = new HousingSponsoring();
            $housingSponsoring->housing_id = $request->housing_id;
            $housingSponsoring->sponsoring_id = $request->sponsoring_id;
            $housingSponsoring->date_debut = $dateDebut;
            $housingSponsoring->date_fin = $dateFin;
            $housingSponsoring->nombre = $request->sponsoring_id;
            $housingSponsoring->is_actif = false;
            $housingSponsoring->save();

            $payement = new Payement();
            $payement->amount = $request->montant;
            $payement->payment_method = $request->payment_method;
            $payement->id_transaction = $request->id_transaction;
            $payement->statut = $request->statut_paiement;
            $payement->housing_sponsoring_id = $housingSponsoring->id;
            $payement->is_confirmed = false;
            $payement->is_canceled = false;
            $payement->save();


            if($request->payment_method =='portfeuille'){
                $portefeuille = Portfeuille::where('user_id', Auth::user()->id)->first();
                $portefeuille->solde -= $request->montant;

                $portefeuilleTransaction = new Portfeuille_transaction();
                $portefeuilleTransaction->debit = true;
                $portefeuilleTransaction->credit = false;
                $portefeuilleTransaction->amount = $request->montant;
                $portefeuilleTransaction->motif = "Demande de sponsoring effectuée avec portefeuille";
                $portefeuilleTransaction->housing_sponsoring_id = $housingSponsoring->id;
                $portefeuilleTransaction->payment_method = $request->payment_method;
                $portefeuilleTransaction->id_transaction = $request->id_transaction;
                $portefeuilleTransaction->portfeuille_id = $portefeuille->id;

                $portefeuilleTransaction->save();
                $portefeuille->save();

                (new ReservationController())->initialisePortefeuilleTransaction($portefeuilleTransaction->id);
            }

            DB::commit();

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
            DB::rollBack();
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/hoteActiveSponsoringRequest",
     *     summary="Obtenir les demandes de sponsoring actif d'un hôte connecté",
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
     *
     *
     * )
     */
    public function hoteActiveSponsoringRequest()
    {
        try {

            $housingSponsorings = HousingSponsoring::where('is_deleted',false)
            ->where('is_actif',true)
            ->where('is_rejected',false)
            ->get();
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

            return (new ServiceController())->apiResponse(200, $data, 'Liste des demandes de sponsoring actif d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

     /**
     * @OA\Get(
     *     path="/api/housingsponsoring/hoteRejectSponsoringRequest",
     *     summary="Obtenir les demandes de sponsoring d'un hôte connecté rejeté par un administrateur",
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
    public function hoteRejectSponsoringRequest()
    {
        try {

            $housingSponsorings = HousingSponsoring::where('is_deleted',false)
            ->where('is_actif',false)
            ->where('is_rejected',true)
            ->get();
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

            return (new ServiceController())->apiResponse(200, $data, 'Liste des demandes de sponsoring rejetéesd\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/hotePendingSponsoringRequest",
     *     summary="Obtenir les demandes de sponsoring en cours de validation d'un hôte connecté",
     *     tags={"Housing sponsoring"},
     * 
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
    public function hotePendingSponsoringRequest()
    {
        try {

            $housingSponsorings = HousingSponsoring::where('is_deleted',false)
            ->where('is_actif',false)
            ->where('is_rejected',false)
            ->get();
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

            return (new ServiceController())->apiResponse(200, $data, 'Liste des demandes de sponsoring rejetéesd\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/demandeSponsoringNonvalidee",
     *     summary="Obtenir les demandes de sponsoring actif d'un hôte connecté",
     *     tags={"Admin Housing sponsoring"},
     * security={{"bearerAuth": {}}},
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
     *
     *
     * )
     */

    public function demandeSponsoringNonvalidee(){
        try {
            $sponsoringrequests = HousingSponsoring::where('is_actif',false)
            ->where('is_deleted',false)
            ->where('is_rejected',false)
            ->with(['sponsoring','housing'])
            ->get();
            return (new ServiceController())->apiResponse(200, $sponsoringrequests, 'Liste des demandes de sponsoring d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/demandeSponsoringvalidee",
     *     tags={"Admin Housing sponsoring"},
     *     summary="Liste des demandes de sponsoring validées",
     *     description="Retourne la liste des demandes de sponsoring qui ont été validées.",
     *     operationId="demandeSponsoringvalidee",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des demandes de sponsoring validées récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Liste des demandes de sponsoring validées récupérée avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref=""))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Erreur serveur"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function demandeSponsoringvalidee(){
        try {
            $sponsoringrequests = HousingSponsoring::where('is_actif',true)
            ->where('is_deleted',false)
            ->where('is_rejected',false)
            ->with(['sponsoring','housing'])
            ->get();
            return (new ServiceController())->apiResponse(200, $sponsoringrequests, 'Liste des demandes de sponsoring d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/demandeSponsoringsuprimee",
     *     tags={"Admin Housing sponsoring"},
     *     summary="Liste des demandes de sponsoring rejetées",
     *     description="Retourne la liste des demandes de sponsoring qui ont été rejetées.",
     *     operationId="demandeSponsoringrejetee",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des demandes de sponsoring rejetées récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Liste des demandes de sponsoring rejetées récupérée avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref=""))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Erreur serveur"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function demandeSponsoringrejetee(){
        try {
            $sponsoringrequests = HousingSponsoring::where('is_rejected',true)
            ->where('is_actif',false)
            ->where('is_deleted',false)
            ->with(['sponsoring','housing'])
            ->get();
            return (new ServiceController())->apiResponse(200, $sponsoringrequests, 'Liste des demandes de sponsoring d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/demandeSponsoringsupprimee",
     *     tags={"Admin Housing sponsoring"},
     *     summary="Liste des demandes de sponsoring supprimées",
     *     description="Retourne la liste des demandes de sponsoring qui ont été supprimées.",
     *     operationId="demandeSponsoringsupprimee",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des demandes de sponsoring supprimées récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Liste des demandes de sponsoring supprimées récupérée avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref=""))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Erreur serveur"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */


    public function demandeSponsoringsupprimee(){
        try {
            $sponsoringrequests = HousingSponsoring::where('is_deleted',true)
            ->with(['sponsoring','housing'])
            ->get();
            return (new ServiceController())->apiResponse(200, $sponsoringrequests, 'Liste des demandes de sponsoring supprimé d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
 * @OA\Post(
 *     path="/api/housingsponsoring/rejectSponsoringRequest/{sponsorinRequestId}",
 *     summary="Rejeter une demande de sponsoring",
 *     tags={"Admin Housing sponsoring"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="sponsorinRequestId",
 *         in="path",
 *         description="ID de la demande de sponsoring à rejeter",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="motif", type="string", example="Le motif du rejet")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Demande de sponsoring rejetée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Demande de sponsoring rejetée avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Demande de sponsoring introuvable",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Demande de sponsoring introuvable")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur de traitement")
 *         )
 *     )
 * )
 */

    public function rejectSponsoringRequest(Request $request,$sponsorinRequestId){
        try {
                $request->validate([
                    'motif' => 'required'
                ]);
                $housingSponsoring = HousingSponsoring::find($sponsorinRequestId);
                if (!$housingSponsoring) {
                    return (new ServiceController())->apiResponse(404, [], 'Demande de sponsoring introuvable');
                }

                if($housingSponsoring->is_actif == true){
                    return (new ServiceController())->apiResponse(200, [],'Vous ne pouvez rejeté une demande qui est déjà activé, veuillez la désactivé');
                }

                if($housingSponsoring->is_rejected == true){
                    return (new ServiceController())->apiResponse(200, [],'Cette demande a déjà été rejeté');
                }
                DB::beginTransaction();

                $hote = Housing::whereId($housingSponsoring->housing_id)->first()->user;
                $sponsoring = Sponsoring::find($housingSponsoring->sponsoring_id);
                $prix_tarif = $housingSponsoring->nombre * $sponsoring->prix;

                $portfeuille =Portfeuille::where('user_id',$hote->id)->first();
                $portfeuille->where('user_id',$hote->id)->update(['solde'=> $portfeuille->solde + $prix_tarif]);
                $transaction = new portfeuille_transaction();
                $transaction->portfeuille_id = $portfeuille->id;
                $transaction->amount = $prix_tarif;
                $transaction->debit = 0;
                $transaction->credit =1;
                $transaction->housing_sponsoring_id = $housingSponsoring->id;
                $transaction->payment_method = "portfeuille";
                $transaction->motif = "Remboursement suite à un rejet de la demande par un administateur";
                $transaction->save();
                (new ReservationController())->initialisePortefeuilleTransaction($transaction->id);

                $housingSponsoring->is_rejected = true;
                $housingSponsoring->motif = $request->motif;
                $housingSponsoring->save();
                DB::commit();

                $hotemail = [
                    'title' => "Rejet de votre demande de sponsoring",
                    "body" => "$request->motif. Votre portefeuille a été crédité. Nouveau solde $portfeuille->solde "
                ];

            dispatch( new SendRegistrationEmail($hote->email, $hotemail['body'], $hotemail['title'], 2));

            return (new ServiceController())->apiResponse(200,[] ,'Sponsoring rejeté avec succès');
        } catch (Exception $e) {
            DB::rollBack();
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


     /**
     * @OA\Post(
     *     path="/api/housingsponsoring/invalidSponsoringRequest/{sponsorinRequestId}",
     *     summary="Désactiver une demande de sponsoring",
     *     tags={"Admin Housing sponsoring"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sponsorinRequestId",
     *         in="path",
     *         description="ID de la demande de sponsoring à désactiver",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="motif", type="string", example="Le motif du rejet")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Demande de sponsoring désactivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Demande de sponsoring désactivé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Demande de sponsoring introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Demande de sponsoring introuvable")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erreur de traitement")
     *         )
     *     )
     * )
     */
    public function invalidSponsoringRequest(Request $request,$sponsorinRequestId){
        try {
                $request->validate([
                    'motif' => 'required',
                    'montant' => 'required'
                ]);
                $housingSponsoring = HousingSponsoring::find($sponsorinRequestId);
                if (!$housingSponsoring) {
                    return (new ServiceController())->apiResponse(404, [], 'Demande de sponsoring introuvable');
                }

                if($housingSponsoring->is_rejected == true){
                    return (new ServiceController())->apiResponse(200,[], 'Vous ne pouvez désactiver une demande rejeté');
                }

                if($housingSponsoring->is_actif == false){
                    return (new ServiceController())->apiResponse(200,[],'Cette demande a déjà été désactivé');
                }

                if(!is_numeric($request->montant)){
                    return (new ServiceController())->apiResponse(404, [], 'Le montant doit être un entier');
                }
    
                if($request->montant<=0){
                    return (new ServiceController())->apiResponse(404, [], 'Le montant doit être supérieur à 0 ');
                }


                DB::beginTransaction();

                $hote = Housing::whereId($housingSponsoring->housing_id)->first()->user;
                $prix_tarif = $request->montant;

                $portfeuille =Portfeuille::where('user_id',$hote->id)->first();
                $portfeuille->where('user_id',$hote->id)->update(['solde'=> $portfeuille->solde + $prix_tarif]);
                $transaction = new portfeuille_transaction();
                $transaction->portfeuille_id = $portfeuille->id;
                $transaction->amount = $prix_tarif;
                $transaction->debit = 0;
                $transaction->credit =1;
                $transaction->housing_sponsoring_id = $housingSponsoring->id;
                $transaction->payment_method = "portfeuille";
                $transaction->motif = "Remboursement suite à un rejet de la demande par un administateur";
                $transaction->save();
                (new ReservationController())->initialisePortefeuilleTransaction($transaction->id);

                $housingSponsoring->is_actif = false;
                $housingSponsoring->motif = $request->motif;


                $housingSponsoring->save();

                DB::commit();

                $hotemail = [
                    'title' => "Rejet de votre demande de sponsoring",
                    "body" => "$request->motif. Votre portefeuille a été crédité. Nouveau solde $portfeuille->solde "
                ];

            dispatch( new SendRegistrationEmail($hote->email, $hotemail['body'], $hotemail['title'], 2));

            return (new ServiceController())->apiResponse(200,[],'Sponsoring désactivé avec succès');
        } catch (Exception $e) {
            DB::rollBack();
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
     * @OA\Post(
     *     path="/api/housingsponsoring/validSponsoringRequest/{sponsorinRequestId}",
     *     tags={"Admin Housing sponsoring"},
     *     summary="Valider une demande de sponsoring",
     *     description="Cette fonction permet de valider une demande de sponsoring en activant la demande et en envoyant un email de confirmation à l'hôte.",
     *     operationId="validSponsoringRequest",
     *     @OA\Parameter(
     *         name="sponsorinRequestId",
     *         in="path",
     *         description="ID de la demande de sponsoring",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Demande de sponsoring activée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Demande de sponsoring activée avec succès"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Demande de sponsoring introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Demande de sponsoring introuvable"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Erreur serveur"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function validSponsoringRequest($sponsorinRequestId){
        try {

            $housingSponsoring = HousingSponsoring::find($sponsorinRequestId);
                if (!$housingSponsoring) {
                    return (new ServiceController())->apiResponse(404, [], 'Demande de sponsoring introuvable');
                }

                $hote = Housing::whereId($housingSponsoring->housing_id)->first()->user;

                $housingSponsoring->is_actif = true;
                $housingSponsoring->save();

                $jour_demande = Sponsoring::find($housingSponsoring->sponsoring_id)->created_at;

                $hotemail = [
                    'title' => "Confirmation de l'activation de votre demande de sponsoring",
                    "body" => "Félicitation !!! Votre demande de sponsoring concernant que vous avez fait le {$jour_demande} "
                ];

            dispatch( new SendRegistrationEmail($hote->email, $hotemail['body'], $hotemail['title'], 2));

            return (new ServiceController())->apiResponse(200,[],'Demande de sponsoring activé avec succès');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

}
