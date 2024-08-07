<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Models\Charge;
use App\Models\Housing;
use App\Models\housing_preference;
use App\Models\reduction;
use App\Models\promotion;
use App\Models\photo;
use App\Models\housing_price;
use App\Models\File;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Equipment_category;
use App\Models\Housing_equipment;
use App\Models\Housing_category_file;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as F;
use App\Models\Category;
use App\Models\Housing_charge;
use App\Models\Review_reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use DateTime;
use Exception;
use Illuminate\Http\Response;

class PromotionController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/promotion/add",
 *     summary="Ajouter une nouvelle promotion",
 *     tags={"Promotion hote"},
 *     security={{"bearerAuth": {}}}, 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"housing_id", "number_of_reservation", "value", "date_debut", "date_fin"},
 *             @OA\Property(property="housing_id", type="integer", description="ID du logement"),
 *             @OA\Property(property="number_of_reservation", type="integer", description="Nombre de réservations"),
 *             @OA\Property(property="value", type="number", format="float", description="Valeur de la promotion"),
 *             @OA\Property(property="date_debut", type="string", format="date", description="Date de début de la promotion"),
 *             @OA\Property(property="date_fin", type="string", format="date", description="Date de fin de la promotion"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Promotion ajoutée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Promotion ajoutée avec succès."),
 *             @OA\Property(property="promotion", type="object", 
 *                 @OA\Property(property="housing_id", type="integer"),
 *                 @OA\Property(property="number_of_reservation", type="integer"),
 *                 @OA\Property(property="value", type="number", format="float"),
 *                 @OA\Property(property="is_encours", type="boolean"),
 *                 @OA\Property(property="date_debut", type="string", format="date"),
 *                 @OA\Property(property="date_fin", type="string", format="date"),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Promotion en cours",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Ce logement a déjà une promotion en cours."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Données non valides",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object",
 *                 @OA\Property(property="housing_id", type="string", example="Le logement est requis."),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
 *         ),
 *     ),
 * )
 */

 public function addPromotion(Request $request)
{
    $validator = Validator::make($request->all(), [
        'housing_id' => 'required|exists:housings,id',
        'number_of_reservation' => 'required|integer',
        'value' => 'required|numeric',
        'date_debut' => 'required|date',
        'date_fin' => 'required|date',
    ]);

    $message = [];

    if ($validator->fails()) {
        $message[] = $validator->errors();
        return (new ServiceController())->apiResponse(505,[],$message);
    }

    if(intval($request->number_of_reservation) <=0){
        return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur du nombre de réservation  soit positive et non nulle");
    }

    if(floatval($request->value)<= 0){
        return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur de la commission du  soit positive et non nulle");
    }

    if (!strtotime($request->date_debut)) {
        return (new ServiceController())->apiResponse(404, [], 'La date de début de la promotion doit être une date valide.');
    }
    if (!strtotime($request->date_fin)) {
        return (new ServiceController())->apiResponse(404, [], 'La date de fin de la promotion doit être une date valide.');
    }

    $dateDebut = new DateTime($request->date_debut);
    $dateFin = new DateTime($request->date_fin);

    if ($dateFin <= $dateDebut) {
        return (new ServiceController())->apiResponse(404, [], "La date de fin doit être postérieure à la date de début.");
    }

    $housingId = $request->housing_id;

    $dateToday = new DateTime();

    if ($dateDebut->format('Y-m-d') < $dateToday->format('Y-m-d')) {
        return (new ServiceController())->apiResponse(404, [], 'La date de début est déjà passée.');
    }

    $existingPromotion = Promotion::where('housing_id', $housingId)
        ->where('is_deleted', false)
        ->where('is_blocked', false)
        ->whereBetween('date_fin', [$dateDebut, $dateFin])
        ->first();

    if ($existingPromotion) {
        return (new ServiceController())->apiResponse(404, [$existingPromotion], "Chevauchement de dates avec une promotion existante.Il ne peut pas avoir deux promotions pendant la même periode pour un logement donné ");
    }

    $isEncours = $dateDebut->format('Y-m-d') === $dateToday->format('Y-m-d');

    $promotion = new Promotion([
        'housing_id' => $request->housing_id,
        'number_of_reservation' => $request->number_of_reservation,
        'value' => $request->value,
        'is_encours' => $isEncours,
        'date_debut' => $dateDebut,
        'date_fin' => $dateFin,
    ]);

    $promotion->save();

    return (new ServiceController())->apiResponse(200, [$promotion], "Promotion ajoutée avec succès.");
}

/**
 * @OA\Get(
 *     path="/api/promotion/user",
 *     summary="Obtenir les promotions d'un utilisateur connecté(Dashboard hote)",
 *     tags={"Promotion hote"},
 *     security={{"bearerAuth": {}}}, 
 *     @OA\Response(
 *         response=200,
 *         description="Promotions associées à l'utilisateur qui est connecté actuellement",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="housing_id", type="integer", example=2),
 *                     @OA\Property(property="number_of_reservation", type="integer", example=10),
 *                     @OA\Property(property="value", type="number", format="float", example=15.5),
 *                     @OA\Property(property="is_encours", type="boolean", example=true),
 *                     @OA\Property(property="date_debut", type="string", format="date", example="2024-01-01"),
 *                     @OA\Property(property="date_fin", type="string", format="date", example="2024-01-31"),
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Utilisateur non connecté",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Utilisateur non connecté."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
 *         ),
 *     ),
 * )
 */

    public function getUserPromotions()
  {
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non connecté.'], Response::HTTP_UNAUTHORIZED);
        }

        $housings = $user->housing;

        if ($housings->isEmpty()) {
            return response()->json(['data' => 'Aucun logement trouvé pour cet utilisateur.'], Response::HTTP_OK);
        }

        $promotions = [];

        foreach ($housings as $housing) {
            $housingPromotions = Promotion::where('housing_id', $housing->id)->get();

            $promotions = array_merge($promotions, $housingPromotions->toArray());
        }

        return response()->json(['data' => $promotions], Response::HTTP_OK);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

/**
 * @OA\Get(
 *     path="/api/promotion/housing/{housingId}",
 *     summary="Obtenir toutes les promotions d'un logement donné",
 *     tags={"Promotion hote"},
 *     security={{"bearerAuth": {}}}, 
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement",
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Toutes les promotions associées au logement",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="housing_id", type="integer"),
 *                     @OA\Property(property="number_of_reservation", type="integer"),
 *                     @OA\Property(property="value", type="float"),
 *                     @OA\Property(property="is_encours", type="boolean"),
 *                     @OA\Property(property="date_debut", type="string", format="date"),
 *                     @OA\Property(property="date_fin", type="string", format="date"),
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Logement non trouvé."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
 *         ),
 *     ),
 * )
 */


   public function getHousingPromotions($housingId)
  {
    try {
        $housing = Housing::find($housingId);
        if (!$housing) {
            return response()->json(['error' => 'Logement non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $promotions = Promotion::where('housing_id', $housingId)->get();

        return response()->json(['data' => $promotions], Response::HTTP_OK);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
 * @OA\Get(
 *     path="/api/promotion/all",
 *     summary="Obtenir toutes les promotions sur le site avec détails du logement et de l'utilisateur(Dashboard Admin)",
 *     tags={"Promotion hote"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste de toutes les promotions avec les détails du logement et de l'utilisateur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="housing", type="object",
 *                         @OA\Property(property="id", type="integer"),
 *                         @OA\Property(property="name", type="string"),
 *                         @OA\Property(property="address", type="string"),
 *                     ),
 *                     @OA\Property(property="housing.user", type="object",
 *                         @OA\Property(property="id", type="integer"),
 *                         @OA\Property(property="firstname", type="string"),
 *                         @OA\Property(property="lastname", type="string"),
 *                     ),
 *                     @OA\Property(property="number_of_reservation", type="integer"),
 *                     @OA\Property(property="value", type="float"),
 *                     @OA\Property(property="is_encours", type="boolean"),
 *                     @OA\Property(property="date_debut", type="string", format="date"),
 *                     @OA\Property(property="date_fin", type="string", format="date"),
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
 *         ),
 *     ),
 * )
 */

  public function getAllPromotions()
  {
    try {

        $promotions = Promotion::with(['housing', 'housing.user'])->get();

        return response()->json(['data' => $promotions], Response::HTTP_OK);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

/**
 * @OA\Delete(
 *     path="/api/promotion/delete/{id}",
 *     summary="Supprimer une promotion par ID",
 *     tags={"Promotion hote"},
 *     security={{"bearerAuth": {}}}, 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de la promotion à supprimer",
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Promotion supprimée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Promotion supprimée avec succès."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Promotion non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Promotion non trouvée."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
 *         ),
 *     ),
 * )
 */
public function DeletePromotion($id)
{
    try {
        $promotion = promotion::find($id);

        if (!$promotion) {
            return response()->json(['error' => 'Promotion non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        if ($promotion->is_deleted) {
            return response()->json(['error' => 'Promotion déjà supprimée.'], Response::HTTP_CONFLICT);
        }

        $promotion->update(['is_deleted' => true]);

        return response()->json(['message' => 'Promotion supprimée avec succès.'], Response::HTTP_OK);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}




/**
 * @OA\Post(
 *     path="/api/promotion/active/{promotionId}/{housingId}",
 *     summary="Active une promotion pour un logement",
 *     tags={"Promotion hote"},
 *     @OA\Parameter(
 *         name="promotionId",
 *         in="path",
 *         required=true,
 *         description="ID de la promotion à activer",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="housingId",
 *         in="path",
 *         required=true,
 *         description="ID du logement pour lequel activer la promotion",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Promotion activée avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 description="Statut de la réponse",
 *                 type="integer"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 description="Message de succès",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Logement ou promotion non trouvés ou promotion déjà activée",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 description="Statut de la réponse",
 *                 type="integer"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="status",
 *                 description="Statut de la réponse",
 *                 type="integer"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 description="Message d'erreur",
 *                 type="string"
 *             )
 *         )
 *     ),
 *     security={
 *         {"bearerAuth": {}}
 *     }
 * )
 */

public function activePromotion($promotionId, $housingId) {
        try {
            $housing = Housing::find($housingId);
            if (!$housing) {
                return (new ServiceController())->apiResponse(404, [], 'Logement non trouvé.');
            }

            $promotion = Promotion::find($promotionId);
            if (!$promotion) {
                return (new ServiceController())->apiResponse(404, [], 'Promotion non trouvée.');
            }

            $dateDebut = new DateTime($promotion->date_debut);
            $dateFin = new DateTime($promotion->date_fin);
            $dateToday = new DateTime();

            if (($dateToday >= $dateDebut && $dateToday <= $dateFin)) {
                return (new ServiceController())->apiResponse(404, [], 'La date actuelle n\'est pas dans la période de validité de la promotion.');
            }

            if ($promotion->is_encours) {
                return (new ServiceController())->apiResponse(404, [], 'La promotion est déjà activée.');
            }

            $promotion->is_encours = true;
            $promotion->save();

            return (new ServiceController())->apiResponse(200, [], 'Promotion activée avec succès.');

        } catch (\Exception $e) {
            return (new ServiceController())->apiResponse(500, [], 'Erreur serveur : ' . $e->getMessage());
        }
    }

}
