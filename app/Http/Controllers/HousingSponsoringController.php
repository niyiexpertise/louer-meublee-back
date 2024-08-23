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
use App\Services\FileService;
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
 *     tags={"Hote Housing Sponsoring"},
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
 *               @OA\Property(property="id_transaction", type="string", example="txjshg4sp", description="id de la transaction."),
 *               @OA\Property(property="statut_paiement", type="string", example=1, description="Statut du paiement."),
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

           

            $dateDebut = Carbon::parse($request->date_debut);

            if ($dateDebut->lessThanOrEqualTo(Carbon::now())) {
                return (new ServiceController())->apiResponse(404, [], 'La date de début doit être supérieur à la date d\'aujourd\'hui');
            }

            $duree= $nombre * $sponsoring->duree;
            $prix_tarif = $nombre * $sponsoring->prix;
            $montant = $request->montant??$prix_tarif;

            $dateFin = $dateDebut->copy()->addDays($duree);

            $exists = HousingSponsoring::where('housing_id',$request->housing_id)->where('sponsoring_id',$request->sponsoring_id)->where('is_deleted',false)->where('is_rejected',false)->get();


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
                    return (new ServiceController())->apiResponse(404, [], "Solde insuffisant. Veuillez recharger votre portefeuille. Vous devez disposer de $prix_tarif XOF/FCFA ");
                };

            }else{

                if(!$request->montant){
                    return (new ServiceController())->apiResponse(404, [], 'Veuillez renseigner le montant du paiement');
                }

                if(!is_numeric($request->montant)){
                    return (new ServiceController())->apiResponse(404, [], 'Le montant doit être un entier');
                }
    
                if($request->montant<=0){
                    return (new ServiceController())->apiResponse(404, [], 'Le montant doit être supérieur à 0 ');
                }

                if($request->montant < $prix_tarif){
                    return (new ServiceController())->apiResponse(404, [], "Le montant est insuffisant. Vous devez payer $prix_tarif XOF/FCFA");
                }

                $existTransaction = Payement::where('id_transaction',$request->id_transaction)->exists();
                if ($existTransaction) {
                    return (new ServiceController())->apiResponse(404, [], 'L\'id de la transaction exise déjà');
                }
            }

            $housingSponsoring = new HousingSponsoring();
            $housingSponsoring->housing_id = $request->housing_id;
            $housingSponsoring->sponsoring_id = $request->sponsoring_id;
            $housingSponsoring->date_debut = $dateDebut;
            $housingSponsoring->date_fin = $dateFin;
            $housingSponsoring->nombre = $nombre;
            $housingSponsoring->duree = $sponsoring->duree;
            $housingSponsoring->prix = $sponsoring->prix;
            $housingSponsoring->description = $sponsoring->description;
            $housingSponsoring->is_actif = false;
            $housingSponsoring->save();

            $payement = new Payement();
            $payement->amount =  $montant;
            $payement->payment_method = $request->payment_method;
            $payement->id_transaction = $request->id_transaction;
            $payement->statut = $request->statut_paiement;
            $payement->housing_sponsoring_id = $housingSponsoring->id;
            $payement->is_confirmed = false;
            $payement->is_canceled = false;
            $payement->save();


            if($request->payment_method =='portfeuille'){
                $portefeuille = Portfeuille::where('user_id', Auth::user()->id)->first();
                $portefeuille->solde -=  $prix_tarif;
                $transaction = new Portfeuille_transaction();
                $transaction->debit = true;
                $transaction->credit = false;
                $transaction->amount =  $prix_tarif;
                $transaction->motif = "Demande de sponsoring effectuée avec portefeuille";
                $transaction->housing_sponsoring_id = $housingSponsoring->id;
                $transaction->payment_method = $request->payment_method;
                $transaction->id_transaction = $request->id_transaction;
                $transaction->portfeuille_id = $portefeuille->id;

                $transaction->save();
                $portefeuille->save();

                // (new ReservationController())->initialiseTransaction($transaction->id);
            }else{
                $commission_amount = 0 ;
                $remaining_amount = 0;

                $previous_transactions = Portfeuille_transaction::all();
                $solde_total = Portfeuille_transaction::where('credit', true)->sum('amount')-Portfeuille_transaction::where('debit', true)->sum('amount');
                $solde_commission = $previous_transactions->sum('montant_commission');
                $solde_restant = $previous_transactions->sum('montant_restant');
                $solde_commission_admin = $previous_transactions->sum('montant_commission_admin');
                $solde_commission_partenaire = $previous_transactions->sum('montant_commission_partenaire');

                $new_solde_commission = $solde_commission + $commission_amount;
                $new_solde_restant = $solde_restant + $remaining_amount;
                $new_solde_total = $solde_total + $prix_tarif;
                $new_solde_partenaire = $solde_commission_partenaire+ 0;

                $new_solde_total = $solde_total + $montant;

                $transaction = new Portfeuille_transaction();
                $transaction->credit = true;
                $transaction->debit = false;
                $transaction->amount =  $montant;
                $transaction->valeur_commission = 0;
                $transaction->montant_commission = $commission_amount;
                $transaction->montant_commission_partenaire =0;
                $transaction->valeur_commission_partenaire=0;
                $transaction->valeur_commission_admin=0;
                $transaction->montant_commission_admin= 0;
                $transaction->solde_total = $new_solde_total;
                $transaction->motif = "Demande de sponsoring effectuée avec un autre moyen autre que le portfeuille";
                $transaction->solde_commission = $new_solde_commission;
                $transaction->solde_restant = $new_solde_restant;
                $transaction->new_solde_admin=$solde_commission_admin;
                $transaction->solde_commission_partenaire=$new_solde_partenaire;
                $transaction->housing_sponsoring_id = $housingSponsoring->id;
                $transaction->payment_method = $request->payment_method;
                $transaction->id_transaction = $request->id_transaction;
                $transaction->save();
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
     *     path="/api/housingsponsoring/hoteSponsoringRequest",
     *     summary="Obtenir les demandes de sponsoring d'un hôte connecté",
     *     tags={"Hote Housing Sponsoring"},
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
    public function hoteSponsoringRequest()
    {
        try {

            $housingSponsorings = HousingSponsoring::where('is_deleted',false)
            ->get();
            $data = [];

            foreach ($housingSponsorings as $housingSponsoring) {
                if(Housing::whereId($housingSponsoring->housing_id)->first()->user_id == Auth::user()->id){
                    $data[] = [
                        'id' => $housingSponsoring->id,
                        'duree' => $housingSponsoring->duree,
                        'prix_unitaire' => $housingSponsoring->prix,
                        'prix_total' => $housingSponsoring->nombre * $housingSponsoring->prix,
                        'description' => $housingSponsoring->description,
                        'nombre_de_fois' =>  $housingSponsoring->nombre,
                        'Jour_de_la_demande' => $housingSponsoring->created_at,
                        'date_de_commencement_du_sponsoring'=>  $housingSponsoring->date_debut,
                        'date de fin du sponsoring' =>  $housingSponsoring->date_fin,
                    ];
                }
            }

            return (new ServiceController())->apiResponse(200, $data, 'Liste des demandes de sponsoring d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
 * @OA\Post(
 *      path="/api/housingsponsoring/hoteSupprimeDemande/{sponsorinRequestId}",
 *     operationId="hoteSupprimeDemande",
 *     tags={"Hote Housing Sponsoring"},
 *     summary="Supprimer une demande de sponsoring par un hôte",
 *     description="Permet à un hôte de supprimer une demande de sponsoring qui n'est pas encore active.",
 *     @OA\Parameter(
 *         name="sponsorinRequestId",
 *         in="path",
 *         required=true,
 *         description="ID de la demande de sponsoring",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Demande de sponsoring supprimée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=200),
 *             @OA\Property(property="data", type="array", @OA\Items(type="string"), example={}),
 *             @OA\Property(property="message", type="string", example="Demande de sponsoring supprimée avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Demande de sponsoring introuvable ou déjà active",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=404),
 *             @OA\Property(property="data", type="array", @OA\Items(type="string"), example={}),
 *             @OA\Property(property="message", type="string", example="Demande de sponsoring introuvable")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="integer", example=500),
 *             @OA\Property(property="data", type="array", @OA\Items(type="string"), example={}),
 *             @OA\Property(property="message", type="string", example="Erreur serveur")
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

    public function hoteSupprimeDemande($sponsorinRequestId){
        try {

            $housingSponsoring = HousingSponsoring::find($sponsorinRequestId);
                if (!$housingSponsoring) {
                    return (new ServiceController())->apiResponse(404, [], 'Demande de sponsoring introuvable');
                }

                if ($housingSponsoring->is_actif == true) {
                    return (new ServiceController())->apiResponse(404, [], 'Vous ne pouvez pas supprimé une demande de sponsoring déjà active');
                }

                if ($housingSponsoring->is_deleted == true) {
                    return (new ServiceController())->apiResponse(404, [], 'Demande de sponsoring déjà supprimée');
                }
                $hote = Housing::whereId($housingSponsoring->housing_id)->first()->user;

                if ($hote->id != Auth::user()->id) {
                    return (new ServiceController())->apiResponse(404, [], 'Vous ne pouvez pas supprimé une demande de sponsoring qui ne vous appartient pas');
                }

                $housingSponsoring->is_deleted = true;
                $housingSponsoring->save();

            return (new ServiceController())->apiResponse(200,[],'Demande de sponsoring supprimé avec succès');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/demandeSponsoringNonvalidee",
     *     summary="Obtenir les demandes de sponsoring actif d'un hôte connecté",
     *     tags={"Admin Housing Sponsoring"},
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
            // ->with(['sponsoring','housing'])
            ->get();

            foreach($sponsoringrequests as $sponsoringrequest){
                $sponsoringrequest->proprietaire_id = Housing::find($sponsoringrequest->housing_id)->user->id??null;
                $sponsoringrequest->proprietaire_nom = Housing::find($sponsoringrequest->housing_id)->user->firstname??null;
                $sponsoringrequest->proprietaire_prenom = Housing::find($sponsoringrequest->housing_id)->user->lastname??null;
                $sponsoringrequest->proprietaire_email = Housing::find($sponsoringrequest->housing_id)->user->email??null;
                $sponsoringrequest->detail_tarif = Sponsoring::find($sponsoringrequest->sponsoring_id)??null;
            }

            return (new ServiceController())->apiResponse(200, $sponsoringrequests, 'Liste des demandes de sponsoring d\'un hôte connecté');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/housingsponsoring/demandeSponsoringvalidee",
     *     tags={"Admin Housing Sponsoring"},
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
     *     path="/api/housingsponsoring/demandeSponsoringrejetee",
     *     tags={"Admin Housing Sponsoring"},
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
     *     tags={"Admin Housing Sponsoring"},
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
 *     tags={"Admin Housing Sponsoring"},
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
                $commission_amount = 0;
                $remaining_amount = 0;

                $previous_transactions = Portfeuille_transaction::all();
                $solde_total = Portfeuille_transaction::where('credit', true)->sum('amount')-Portfeuille_transaction::where('debit', true)->sum('amount');
                $solde_commission = $previous_transactions->sum('montant_commission');
                $solde_restant = $previous_transactions->sum('montant_restant');
                $solde_commission_admin = $previous_transactions->sum('montant_commission_admin');
                $solde_commission_partenaire = $previous_transactions->sum('montant_commission_partenaire');

                $new_solde_commission = $solde_commission + $commission_amount;
                $new_solde_restant = $solde_restant + $remaining_amount;
                $new_solde_total = $solde_total + $prix_tarif;
                $new_solde_partenaire = $solde_commission_partenaire+ 0;


                $portfeuille =Portfeuille::where('user_id',$hote->id)->first();
                $portfeuille->solde = $portfeuille->solde + $prix_tarif;
                // $portfeuille->update(['solde'=> $portfeuille->solde + $prix_tarif]);
                $transaction = new portfeuille_transaction();
                $transaction->portfeuille_id = $portfeuille->id;
                $transaction->amount = $prix_tarif;
                $transaction->montant_restant = $remaining_amount;
                $transaction->debit = true;
                $transaction->credit =false;
                $transaction->valeur_commission = 0;
                $transaction->montant_commission = $commission_amount;
                $transaction->valeur_commission_admin=0;
                $transaction->montant_commission_admin= $commission_amount;
                $transaction->montant_commission_partenaire =0;
                $transaction->valeur_commission_partenaire=0;
                $transaction->solde_total = $new_solde_total;
                $transaction->solde_commission = $new_solde_commission;
                $transaction->solde_restant = $new_solde_restant;
                $transaction->new_solde_admin=$solde_commission_admin;
                $transaction->solde_commission_partenaire=$new_solde_partenaire;
                $transaction->housing_sponsoring_id = $housingSponsoring->id;
                $transaction->id_transaction = "0";
                $transaction->payment_method = "portfeuille";
                $transaction->motif = "Remboursement suite à un rejet de la demande par un administateur";
                $transaction->save();
                $portfeuille->save();
                (new ReservationController())->initialiseTransaction($transaction->id);

                $housingSponsoring->is_rejected = true;
                $housingSponsoring->motif = $request->motif;
                $housingSponsoring->save();
                DB::commit();

                $hotemail = [
                    'title' => "Rejet de votre demande de sponsoring",
                    "body" => "$request->motif. Votre portefeuille a été crédité. Nouveau solde". Portfeuille::where('user_id',$hote->id)->first()->solde 
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
     *     tags={"Admin Housing Sponsoring"},
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
     *             @OA\Property(property="motif", type="string", example="Le motif du rejet"),
     *             @OA\Property(property="montant", type="string", example=4)
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
                    return (new ServiceController())->apiResponse(200,[],'Cette demande doit être activée avant que vous ne la désactivée');
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
                $portfeuille->update(['solde'=> $portfeuille->solde + $prix_tarif]);
                $transaction = new portfeuille_transaction();
                $transaction->portfeuille_id = $portfeuille->id;
                $transaction->amount = $prix_tarif;
                $transaction->debit = 0;
                $transaction->credit =1;
                $transaction->housing_sponsoring_id = $housingSponsoring->id;
                $transaction->payment_method = "portfeuille";
                $transaction->motif = "Remboursement suite à un rejet de la demande par un administateur";
                $transaction->save();
                (new ReservationController())->initialiseTransaction($transaction->id);

                $housingSponsoring->is_actif = false;
                $housingSponsoring->motif = $request->motif;


                $housingSponsoring->save();

                DB::commit();

                $hotemail = [
                    'title' => "Rejet de votre demande de sponsoring",
                    "body" => "$request->motif. Votre portefeuille a été crédité. Nouveau solde". Portfeuille::where('user_id',$hote->id)->first()->solde
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
     *     tags={"Admin Housing Sponsoring"},
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

                if ($housingSponsoring->is_actif == true) {
                    return (new ServiceController())->apiResponse(404, [], 'Demande de sponsoring déjà actif');
                }

                $sponsoring = Sponsoring::find($housingSponsoring->sponsoring_id);

                if (!$sponsoring) {
                    return (new ServiceController())->apiResponse(404, [], ' tarif de sponsoring non trouvé');
                }

                $hote = Housing::whereId($housingSponsoring->housing_id)->first()->user;

                $housingSponsoring->is_actif = true;
                $housingSponsoring->save();

                $jour_demande = Carbon::parse(Sponsoring::find($housingSponsoring->sponsoring_id)->created_at)->format('d m Y');
                $plan = $sponsoring->description;
                $nombre = $housingSponsoring->nombre??1;

                $housing = Housing::whereId($housingSponsoring->housing_id)->first();
                $pieces = $housing->housingCategoryFiles;

                $dateDebut = Carbon::parse($housingSponsoring->date_debut);

                if ($dateDebut->lessThanOrEqualTo(Carbon::now())) {
                    return (new ServiceController())->apiResponse(404, [], 'Vous ne pouvez activé une demande dont la date d\'aujourd\'aujourd\'hui est supérieur à la date de commencement du sponsoring');
                }

                $prix_tarif = $nombre * $sponsoring->prix;
                $commission_amount = $prix_tarif ;
                $remaining_amount = 0;

                $previous_transactions = Portfeuille_transaction::all();
                $solde_total = Portfeuille_transaction::where('credit', true)->sum('amount')-Portfeuille_transaction::where('debit', true)->sum('amount');
                $solde_commission = $previous_transactions->sum('montant_commission');
                $solde_restant = $previous_transactions->sum('montant_restant');
                $solde_commission_admin = $previous_transactions->sum('montant_commission_admin');
                $ancien_solde_commission_partenaire = 0;

                $new_solde_commission = $solde_commission + $commission_amount;
                $new_solde_restant = $solde_restant + $remaining_amount;

                $transaction =new Portfeuille_transaction();
                $transaction->debit = true;
                $transaction->credit = false;
                // $n = $transaction->credit == true?1:-1;
                $transaction->amount = $prix_tarif;
                $new_solde_total = $solde_total + $prix_tarif;
                $transaction->valeur_commission = 100;
                $transaction->montant_commission = $commission_amount;
                $transaction->montant_restant = $remaining_amount;
                $transaction->valeur_commission_admin=100;
                $transaction->montant_commission_admin= $transaction->montant_commission;
                $transaction->montant_commission_partenaire =0;
                $transaction->valeur_commission_partenaire=0;
                $transaction->solde_total = $new_solde_total;
                $transaction->solde_commission = $new_solde_commission;
                $transaction->solde_restant = $new_solde_restant;
                $transaction->new_solde_admin=$solde_commission_admin+$transaction->montant_commission;
                $transaction->solde_commission_partenaire=$ancien_solde_commission_partenaire;
                $transaction->motif = "Validation d'une demande de sponsoring";
                $transaction->portfeuille_id = Portfeuille::where('user_id',$hote->id)->first()->id;
                $transaction->id_transaction = "0";
                $transaction->payment_method = "portfeuille";
                $transaction->housing_sponsoring_id = $sponsorinRequestId;
                $transaction->save();


                $description = [];
                foreach ($pieces as $piece) {
                    $categoryName = $piece->category->name; 
                    $description[] = "{$piece->number} x {$categoryName}";
                }

                $piecesDescription = implode(', ', $description);

                $hotemail = [
                    'title' => "Confirmation de l'activation de votre demande de sponsoring",
                    "body" => "Félicitations ! Votre demande de sponsoring effectuée le  **{$jour_demande}** concernant le **plan** : **{$plan}**  que vous avez demandé **{$nombre}** fois pour le logement suivant, qui contient : {$piecesDescription}, a été acceptée.
                    Nous vous remercions pour votre confiance."
                ];

            dispatch( new SendRegistrationEmail($hote->email, $hotemail['body'], $hotemail['title'], 2));

            return (new ServiceController())->apiResponse(200,[],'Demande de sponsoring activé avec succès');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


  /**
 * @OA\Get(
 *     path="/api/housingsponsoring/getSponsoredHousings",
 *     tags={"Home Housing Sponsoring"},
 *     summary="Récupérer les logements sponsorisés",
 *     description="Retourne la liste des logements sponsorisés en se basant sur la date actuelle.",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements sponsorisés retournée avec succès.",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur dans la récupération des logements sponsorisés.",
 *     )
 * )
 */
public function getSponsoredHousings()
{
    $today = date('Y-m-d');

    $this->disableExpiredHousings();

    $sponsoredHousings = DB::table('housing_sponsorings')
        ->where('is_actif', true)
        ->where('is_deleted', false)
        ->where('date_debut', '<=', $today)
        ->where('date_fin', '>=', $today)
        ->get();
    $data = [];

        foreach($sponsoredHousings as $sponsoredHousing){
            $listings = Housing::where('status', 'verified')
            ->whereId($sponsoredHousing->housing_id)
            ->where('is_deleted', 0)
            ->where('is_blocked', 0)
            ->where('is_updated', 0)
            ->where('is_actif', 1)
            ->where('is_destroy', 0)
            ->where('is_finished', 1)
            ->get();

            $fileService = new FileService();

            $data= (new HousingController($fileService))->formatListingsData($listings);
        }

    return (new ServiceController())->apiResponse(200, $data,"Liste des logements sponsorisés retournée avec succès.");
}


/**
 * @OA\Post(
 *     path="/api/housingsponsoring/disableExpiredHousings",
 *     tags={"Home Housing Sponsoring"},
 *     summary="Désactiver les logements dont la date_fin est dépassée",
 *     description="Désactive les logements sponsorisés où la date_fin est déjà passée.",
 *     @OA\Response(
 *         response=200,
 *         description="Les logements expirés ont été désactivés avec succès.",
 *     )
 * )
 */
public function disableExpiredHousings()
{
    $currentDate = date('Y-m-d');

    DB::table('housing_sponsorings')
        ->where('date_fin', '<', $currentDate)
        ->where('is_actif', true)
        ->update(['is_actif' => false]);

}




}
