<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\reduction;
use App\Models\Housing;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
class ReductionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/reduction/add",
     *     summary="Ajouter une nouvelle réduction",
     *     tags={"Reduction hote"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"housing_id", "night_number", "value"},
     *             @OA\Property(property="housing_id", type="integer", description="ID du logement"),
     *             @OA\Property(property="night_number", type="integer", description="Nombre de nuits"),
     *             @OA\Property(property="value", type="number", format="float", description="Valeur de la réduction"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Réduction ajoutée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Réduction ajoutée avec succès."),
     *             @OA\Property(property="reduction", type="object",
     *                 @OA\Property(property="housing_id", type="integer"),
     *                 @OA\Property(property="night_number", type="integer"),
     *                 @OA\Property(property="value", type="number", format="float"),
     *                 @OA\Property(property="is_encours", type="boolean"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Réduction en cours avec même nombre de nuits",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Ce logement a déjà une réduction en cours avec le même nombre de nuits."),
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
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
     *         ),
     *     ),
     * )
     */

    public function addReduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'housing_id' => 'required|exists:housings,id',
            'night_number' => 'required|integer',
            'value' => 'required|numeric|between:0,100',
        ]);
        if(Housing::whereId($request->housing_id)->first()->user_id != Auth::user()->id){
            return (new ServiceController())->apiResponse(404,[],'Vous ne pouvez pas ajouter la réduction à un logement qui ne vous appartient pas.');
        }

        if(intval($request->night_number) <=0){
            return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur du nombre de nuit  soit positive et non nulle");
        }

        if(floatval($request->value)<= 0){
            return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur de la commission du nombre de nuit soit positive et non nulle");
        }

        if(!is_null(Setting::first()->max_night_number)){
            if($request->night_number > Setting::first()->max_night_number){
                return (new ServiceController())->apiResponse(404,[],'Le nombre de nuit doit être inférieur ou égal à '.Setting::first()->max_night_number);
            }
        }

        if(!is_null(Setting::first()->max_value_reduction)){
            if($request->value > Setting::first()->max_value_reduction){
                return (new ServiceController())->apiResponse(404,[],'La valeur de la commission du nombre de nuit doit être inférieur ou égal à '.Setting::first()->max_value_reduction.'%');
            }
        }

        $message = [];

        if ($validator->fails()) {
            $message[] = $validator->errors();
            return (new ServiceController())->apiResponse(505,[],$message);
        }

        $currentReduction = $this->currentReduction($request->housing_id,$request->night_number);
        // return $currentReduction;
        if($currentReduction){
            return $currentReduction;
        }

        $reduction = new reduction([
            'housing_id' => $request->housing_id,
            'night_number' => $request->night_number,
            'value' => $request->value,
            'is_encours' => true,
        ]);

        $reduction->save();

        $data = [ 'reduction' => $reduction];

        return (new ServiceController())->apiResponse(200,$data, 'Réduction ajoutée avec succès.');

    }

    /**
     * @OA\Get(
     *     path="/api/reduction/user",
     *     summary="Obtenir les réductions de l'utilisateur connecté",
     *     tags={"Reduction hote"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
 *         response=200,
 *         description="Réductions associées à l'utilisateur connecté",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="housing_id", type="integer", example=2),
 *                     @OA\Property(property="night_number", type="integer", example=10),
 *                     @OA\Property(property="value", type="number", format="float", example=15.5),
 *                     @OA\Property(property="is_encours", type="boolean", example=true),
 *                     @OA\Property(property="date_debut", type="string", format="date"),
 *                     @OA\Property(property="date_fin", type="string", format="date"),
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

    public function getUserReductions()
    {
        try {
            $user = Auth::user();

            $housings = $user->housing;

            if ($housings->isEmpty()) {
                return (new ServiceController())->apiResponse(404,[], 'Aucun logement trouvé pour cet utilisateur.');
            }
            $reductions = [];

            foreach ($housings as $housing) {
                $housingReductions = reduction::with('housing')->where('is_deleted',false)->where('housing_id', $housing->id)->get();

                $reductions = array_merge($reductions, $housingReductions->toArray());
            }

            $data = ['reductions' => $reductions];
            return (new ServiceController())->apiResponse(200,$data, '');

        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/reduction/housing/{housingId}",
     *     summary="Obtenir toutes les réductions d'un logement donné",
     *     tags={"Reduction hote"},
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
 *         description="Toutes les réductions associées au logement",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="housing_id", type="integer"),
 *                     @OA\Property(property="night_number", type="integer"),
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


   public function getHousingReductions($housingId)
   {
       try {
           $housing = Housing::find($housingId);

           if (!$housing) {
            return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
           }

           $reductions = reduction::where('housing_id', $housingId)->get();

           $data = ['reductions' => $reductions];
           return (new ServiceController())->apiResponse(200,$data, '');

       } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
       }
   }

   /**
 * @OA\Get(
 *     path="/api/reduction/all",
 *     summary="Obtenir toutes les réductions sur le site avec détails du logement et de l'utilisateur(Dashboard Admin)",
 *     tags={"Reduction hote"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste de toutes les réductions avec les détails du logement et de l'utilisateur",
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
 *                     @OA\Property(property="night_number", type="integer"),
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

   public function getAllReductions()
   {
       try {

           $reductions = reduction::with(['housing', 'housing.user'])->get();

           $data = ['reductions' => $reductions];
           return (new ServiceController())->apiResponse(200,$data, '');

       } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
       }
   }

   /**
 * @OA\Post(
 *     path="/api/reduction/update/{reductionId}",
 *     summary="Mise à jour d'une réduction",
 *     description="Met à jour les informations d'une réduction existante pour un logement.",
 *     operationId="updateReduction",
 *     tags={"Reduction hote"},
 *     @OA\Parameter(
 *         name="reductionId",
 *         in="path",
 *         required=true,
 *         description="ID de la réduction à mettre à jour",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="value", type="number", description="Valeur en pourcentage de la réduction"),
 *             @OA\Property(property="night_number", type="integer", description="Nombre de nuits pour la réduction"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Réduction mise à jour avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Réduction mise à jour avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="La réduction spécifiée n'existe pas ou d'autres erreurs",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="La réduction spécifiée n'existe pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
 *         )
 *     ),
 *     security={{"bearerAuth":{}}}
 * )
 */


   public function update(Request $request,$reductionId){
    try {

        $reduction = Reduction::find($reductionId);
        if (!$reduction) {
            return (new ServiceController())->apiResponse(404,[],'La réduction spécifiée n\'existe pas.');
        }

        if(Housing::whereId($reduction->housing_id)->first()->user_id != Auth::user()->id){
            return (new ServiceController())->apiResponse(404,[],'Vous ne pouvez modifier la réduction d\'un logement qui ne vous appartient pas.');
        }

        if($request->has('value')){
            if(floatval($request->value) <= 0){
                return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur de la commission de cette réduction soit positive et non nulle");
            }
        }

       

        if($request->has('night_number')){
            if(intval($request->night_number)<=0){
                return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur du nombre de nuit soit positive et non nulle");
            }
        }
         if($request->value > 100){
            return (new ServiceController())->apiResponse(404,[],'La valeur en pourcentage de la réduction doit être inférieur à 100');
        }
        if(!is_null(Setting::first()->max_night_number)){
            if($request->night_number > Setting::first()->max_night_number){
                return (new ServiceController())->apiResponse(404,[],'Le nombre de nuit doit être inférieur ou égal à '.Setting::first()->max_night_number.'%');
            }
        }
       
           

        if(!is_null(Setting::first()->max_value_reduction)){
            if($request->value > Setting::first()->max_value_reduction){
                return (new ServiceController())->apiResponse(404,[],'La valeur en pourcentage de la réduction doit être inférieur ou égal à '.Setting::first()->max_value_reduction);
            }
        }
        

        $reduction->value= $request->value??$reduction->value;
        $reduction->night_number= $request->night_number??$reduction->night_number;
        $reduction->is_actif= 0;
        $reduction->save();

        return (new ServiceController())->apiResponse(200,[],'Réduction mise à jour avec succès');

    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500, [], $e->getMessage());
    }
  }

   /**
 * @OA\Delete(
 *     path="/api/reduction/delete/{id}",
 *     summary="Supprimer une réduction par ID",
 *     tags={"Reduction hote"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de la réduction à supprimer",
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Réduction supprimée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Réduction supprimée avec succès."),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Réduction non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Réduction non trouvée."),
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
public function DeleteReduction($id)
{
    try {
        $reduction = reduction::find($id);

        if (!$reduction) {
            return (new ServiceController())->apiResponse(404,[], 'Réduction non trouvée');
        }

        if ($reduction->is_deleted) {
            return (new ServiceController())->apiResponse(404,[], 'Réduction déjà supprimée');
        }

        if(Auth::user()->id !=Housing::whereId($reduction->housing_id)->first()->user_id){
            return (new ServiceController())->apiResponse(404,[], 'Cette réduction appartient à un logement qui ne vous appartient pas');
        }

        $reduction->is_deleted = true;
        $reduction->save();
        return (new ServiceController())->apiResponse(200,[], 'Réduction supprimée avec succès.');

    } catch (Exception $e) {
        return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}

//Mettre fin à une reduction dont la date est déjà expirée
public function checkAndCloseExpiredReductions($housingId)
{
    $currentDate = Carbon::now();
    $reductions = Reduction::where('housing_id', $housingId)
        ->where('is_encours', true)
        ->where('is_deleted', false)
        ->where('is_blocked', false)
        ->get();
    $expiredReductions = [];

    foreach ($reductions as $reduction) {
        if ($reduction->date_fin < $currentDate) {
            $reduction->is_encours = false;
            $reduction->save();
            $expiredReductions[] = $reduction;
        }
    }

    return response()->json([
        'message' => 'Reductions expirées mises à jour',
        'expired_reductions' => $expiredReductions,
    ]);
}

//Activer les reductions d'un logement une fois que la date debut est arrivé en mettant is_encours à true
public function activateReductionsForHousing($housingId)
{
    $currentDate = Carbon::now();
    $reductions = Reduction::where('housing_id', $housingId)
        ->where('is_encours', false)
        ->where('is_deleted', false)
        ->where('is_blocked', false)
        ->get();

    $activatedReductions = [];

    foreach ($reductions as $reduction) {
        if ($reduction->date_debut <= $currentDate and $reduction->date_fin >= $currentDate) {
            $reduction->is_encours = true;
            $reduction->save();

            $activatedReductions[] = $reduction;
        }
    }

    if (empty($activatedReductions)) {
        return response()->json([
            'message' => 'Aucune réduction n\'a été activée pour ce logement',
        ], 404);
    }

    return response()->json([
        'message' => 'Reductions activées avec succès',
        'activated_reductions' => $activatedReductions,
    ], 200);
}



    public function checkReduction($reductionId, $housingId){
        $housing = Housing::whereId($housingId)->first();
        $reduction = reduction::whereId($reductionId)->first();

        if(!$housing){
            return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
        }

        if(!$reduction){
            return (new ServiceController())->apiResponse(404,[], 'Réduction non trouvé');
        }

        $errorcheckOwner= (new AddHousingController)->checkOwner($housingId);
        if($errorcheckOwner){
            return $errorcheckOwner;
        }
    }


    public function currentReduction($housing_id, $night_number){
        $currentReductionEnabled = reduction::where('housing_id', $housing_id)
            ->where('night_number', $night_number)
            ->where('is_encours', 1)
            ->where('is_deleted', false)
            ->exists();

            $currentReductionDisabled = reduction::where('housing_id', $housing_id)
            ->where('night_number', $night_number)
            ->where('is_encours', 0)
            ->where('is_deleted', false)
            ->exists();

            if ($currentReductionEnabled) {
                return (new ServiceController())->apiResponse(404,[], 'Ce logement a déjà une réduction en cours avec le même nombre de nuits, vous pouvez modifier la valeur.');
            }

            if ($currentReductionDisabled) {
                return (new ServiceController())->apiResponse(404,[], "Ce logement a déjà une réduction en cours avec le même nombre de nuits mais cette réduction est désactivée. Veuillez l'activez à nouveau.");
            }
    }


//     /**
//  * @OA\Post(
//  *      path="/api/reduction/update/{reductionId}",
//  *      summary="Update a reduction for a housing",
//  *      tags={"Reduction hote"},
//  *      @OA\Parameter(
//  *          name="reductionId",
//  *          in="path",
//  *          required=true,
//  *          description="ID of the reduction to update",
//  *          @OA\Schema(
//  *              type="integer"
//  *          )
//  *      ),
//  *      @OA\RequestBody(
//  *          required=true,
//  *          @OA\MediaType(
//  *              mediaType="application/json",
//  *              @OA\Schema(
//  *                  type="object",
//  *                  required={"value"},
//  *                  @OA\Property(
//  *                      property="value",
//  *                      description="New value for the reduction",
//  *                      type="number",
//  *                      format="float"
//  *                  ),
//  *                  @OA\Property(
//  *                      property="night_number",
//  *                      description="Number of nights for the reduction",
//  *                      type="integer"
//  *                  )
//  *              )
//  *          )
//  *      ),
//  *      @OA\Response(
//  *          response=200,
//  *          description="Reduction updated successfully",
//  *          @OA\JsonContent(
//  *              type="object",
//  *              @OA\Property(
//  *                  property="reductions",
//  *                  description="Details of the updated reduction",
//  *                  type="object",
//  *                  @OA\Property(
//  *                      property="id",
//  *                      description="ID of the reduction",
//  *                      type="integer"
//  *                  ),
//  *                  @OA\Property(
//  *                      property="value",
//  *                      description="Value of the reduction",
//  *                      type="number",
//  *                      format="float"
//  *                  ),
//  *                  @OA\Property(
//  *                      property="night_number",
//  *                      description="Number of nights for the reduction",
//  *                      type="integer"
//  *                  ),
//  *                  @OA\Property(
//  *                      property="housing_id",
//  *                      description="ID of the housing associated with the reduction",
//  *                      type="integer"
//  *                  ),
//  *                  @OA\Property(
//  *                      property="is_encours",
//  *                      description="Status of the reduction",
//  *                      type="boolean"
//  *                  )
//  *              )
//  *          )
//  *      ),
//  *      @OA\Response(
//  *          response=404,
//  *          description="Reduction or housing not found or invalid input",
//  *          @OA\JsonContent(
//  *              type="object",
//  *              @OA\Property(
//  *                  property="message",
//  *                  description="Error message",
//  *                  type="string"
//  *              )
//  *          )
//  *      ),
//  *      @OA\Response(
//  *          response=500,
//  *          description="Server error",
//  *          @OA\JsonContent(
//  *              type="object",
//  *              @OA\Property(
//  *                  property="error",
//  *                  description="Error message",
//  *                  type="string"
//  *              )
//  *          )
//  *      ),
//  *      security={
//  *          {"bearerAuth": {}}
//  *      }
//  * )
//  */

//     public function updateReduction(Request $request,$reductionId){
//         try {

//             $reduction = reduction::find($reductionId);

//             if (!$reduction) {
//                 return (new ServiceController())->apiResponse(404,[], 'Réduction non trouvée');
//             }

//             $housing = Housing::find($reduction->housing_id);

//             if (!$housing) {
//              return (new ServiceController())->apiResponse(404,[], 'Logement non trouvé');
//             }

//             $errorcheckOwner= (new AddHousingController)->checkOwner($housing->id);
//             if($errorcheckOwner){
//                 return $errorcheckOwner;
//             }

//             if(!$request->has('value')){
//                 return (new ServiceController())->apiResponse(404,[], 'commission non renseigné');
//             }

//             if(floatval($request->value) <= 0){
//                 return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur de la commission de cette réduction soit positive et non nulle");
//             }

//             if($request->has('night_number')){
//                 if(intval($request->night_number)<=0){
//                     return (new ServiceController())->apiResponse(404,[], "Assurez vous que la valeur du nombre de nuit soit positive et non nulle");
//                 }
//                 $reduction->night_number = $request->night_number;
//             }

//             $reduction->night_number = $reduction->night_number;
//             $reduction->value = $request->value;
//             $reduction->save();

//             return (new ServiceController())->apiResponse(200,[], 'Réduction modifié avec succès');

//         } catch (Exception $e) {
//          return (new ServiceController())->apiResponse(500,[],$e->getMessage());
//         }
//     }
}

