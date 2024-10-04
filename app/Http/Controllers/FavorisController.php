<?php

namespace App\Http\Controllers;

use App\Models\Favoris;
use Illuminate\Http\Request;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\File;
use App\Models\Notification;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use App\Models\Housing_category_file;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FavorisController extends Controller
{

    public function index()
    {
        //
    }

    /**
 * @OA\Post(
 *     path="/api/logement/addfavorites",
 *     tags={"Favorites"},
 *     summary="Ajouter un logement aux favoris de l'utilisateur",
 *     description="Ajoute un logement aux favoris de l'utilisateur authentifié.",
 *     operationId="addToFavorites",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données requises pour ajouter un logement aux favoris",
 *         @OA\JsonContent(
 *             required={"housing_id"},
 *             @OA\Property(property="housing_id", type="integer", description="ID du logement à ajouter aux favoris")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Le logement a été ajouté aux favoris avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement a été ajouté aux favoris avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Le logement est déjà en favori pour l'utilisateur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement est déjà en favori.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé - L'utilisateur n'est pas authentifié",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de l'ajout aux favoris.")
 *         )
 *     )
 * )
 */


    public function addToFavorites(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'housing_id' => 'required|exists:housings,id',

        ]);

        $message = [];

        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505,[],$message);
        }
        $housing = Housing::where('id', $request->housing_id)
        ->where('status', 'verified')
      ->where('is_deleted', 0)
      ->where('is_blocked', 0)
      ->where('is_updated', 0)
      ->where('is_actif', 1)
      ->where('is_destroy', 0)
      ->where('is_finished', 1)
        ->get();

      if($housing->isEmpty()) {
          return (new ServiceController())->apiResponse(404, [], " L'ID du logement spécifié n'existe pas ou le logement ne respecte pas encore les critère pour être visible sur l'acceuil.");
      }

        try {
            $user = auth()->user();


            if ($user->favorites()->where('housing_id', $request->housing_id)->exists()) {
                return (new ServiceController())->apiResponse(404, [], 'Le logement est déjà en favori.');

            }
            $favorite = new Favoris();
            $favorite->user_id = $user->id;
            $favorite->housing_id = $request->housing_id;
            $favorite->save();

            return (new ServiceController())->apiResponse(200,[], 'Le logement a été ajouté aux favoris avec succès.');

        } catch (\Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }
 /**
 * @OA\Delete(
 *     path="/api/logement/removefromfavorites/{housingId}",
 *     tags={"Favorites"},
 *     summary="Retirer un logement des favoris de l'utilisateur",
 *     description="Retire un logement des favoris de l'utilisateur authentifié.",
 *     operationId="removeFromFavorites",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         description="ID du logement à retirer des favoris",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Le logement a été retiré des favoris avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement a été retiré des favoris avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Le logement n'est pas en favori pour l'utilisateur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Le logement n'est pas en favori.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé - L'utilisateur n'est pas authentifié",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors du retrait des favoris.")
 *         )
 *     )
 * )
 */

public function removeFromFavorites($housingId)
{
    try {
        $user = auth()->user();
        $favorite = $user->favorites()->where('housing_id', $housingId)->first();
        if ($favorite) {
            $favorite->delete();
            return (new ServiceController())->apiResponse(200,[], 'Le logement a été retiré des favoris avec succès.');

        } else {
            return (new ServiceController())->apiResponse(404, [], 'Le logement n\'est pas dans la liste des favoris de cet utilisateur.');

        }
    } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}

    /**
 * @OA\Get(
 *     path="/api/logement/favorites",
 *     tags={"Favorites"},
 *     summary="Récupérer la liste des logements en favoris de l'utilisateur",
 *     description="Récupère la liste des logements en favoris de l'utilisateur authentifié. Seuls les logements qui ne sont ni bloqués ni supprimés seront inclus dans la liste des favoris.",
 *     operationId="getFavorites",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des logements en favoris de l'utilisateur",
 *         @OA\JsonContent(
 *          )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé - L'utilisateur n'est pas authentifié",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la récupération des favoris.")
 *         )
 *     )
 * )
 */

 public function getFavorites()
 {
     try {
         $user = auth()->user();
         $favorite_listings = $user->favorites()
             ->whereHas('housing', function ($query) {
                 $query->where('status', 'verified')
                 ->where('is_deleted', 0)
                 ->where('is_blocked', 0)
                 ->where('is_updated', 0)
                 ->where('is_actif', 1)
                 ->where('is_destroy', 0)
                 ->where('is_finished', 1);
             })
             ->with(['housing', 'housing.photos','housing.user'])
             ->get()
             ->pluck('housing');

             foreach($favorite_listings as $favorite){
                $favorite->is_favorite = true;
                $favorite->housing_note = (new ReviewReservationController())->LogementAvecMoyenneNotesCritereEtCommentairesAcceuil($favorite->id)->original['data']['overall_average'] ?? 'non renseigné';
             }
             $data = (new HousingController())->formatListingsData( $favorite_listings,$user->id);

             $data = [
                'favoris_housing' => $data,

                    ];

            return (new ServiceController())->apiResponse(200,$data, 'Liste des logements ajoutés aux favoris recupérée avec succès  ');

     } catch (\Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
     }
 }

}
