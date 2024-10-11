<?php

namespace App\Http\Controllers;

use App\Models\Payement;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;

class PayementController extends Controller
{
     /**
 * @OA\Get(
 *     path="/api/paiement/reservation/user",
 *     tags={"Paiement"},
 *     summary="Liste des paiements éffectués par un utilisteur connecté",
 *     description="Liste des paiements éffectués par le voyageur.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des paiements éffectués par le voyageur",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucune réservation trouvée pour cet utilisateur"
 *     )
 * )
 */
    public function listPaymentsForUser()
    {
        $userId = Auth::id();
        
        $payments = Payement::where('user_id', $userId)->get();
    
        if ($payments->isEmpty()) {
            return response()->json([
                'message' => 'Aucun paiement trouvé pour cet utilisateur',
            ]);
        }

        foreach($payments as $payment){
            $payment->acteur = $payment->user_id !=null ? "".User::whereId($payment->user_id)->first()->lastname." ". User::whereId($payment->user_id)->first()->firstname : "null"  ;
        }

        
    
        return response()->json([
            'message' => 'Liste des paiements pour l\'utilisateur connecté',
            'data' => $payments,
        ]);
    }

    
     /**
 * @OA\Get(
 *     path="/api/paiement/reservation/all",
 *     tags={"Paiement"},
 *     summary="Liste des paiements éffectués sur le site",
 *     description="Liste des paiements éffectués sur le site.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des paiements éffectués sur le site",
 *         @OA\JsonContent(
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun paiement effectué sur le site"
 *     )
 * )
 */
    
    public function listAllPayments()
    {
        $payments = Payement::all();
    
        if ($payments->isEmpty()) {
            return response()->json([
                'message' => 'Aucun paiement effectué sur le site',
            ], 404);
        }

        foreach($payments as $payment){
            $payment->acteur = $payment->user_id !=null ? "".User::whereId($payment->user_id)->first()->lastname." ". User::whereId($payment->user_id)->first()->firstname : "null"  ;
        }
    
        return response()->json([
            'message' => 'Liste de tous les paiements effectués sur le site',
            'data' => $payments,
        ],200);
    }


}    
