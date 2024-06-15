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
 *     summary="Liste des paiements éffectués par le voyageur lors des reservations",
 *     description="Liste des paiements éffectués par le voyageur lors des reservations.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des paiements éffectués par le voyageur lors des reservations",
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
    
        $reservations = Reservation::where('user_id', $userId)->get();
    
        if ($reservations->isEmpty()) {
            return response()->json([
                'error' => 'Aucune réservation trouvée pour cet utilisateur',
            ], 404);
        }
    
        $payments = Payement::whereIn('reservation_id', $reservations->pluck('id'))->get();
    
        if ($payments->isEmpty()) {
            return response()->json([
                'message' => 'Aucun paiement trouvé pour cet utilisateur',
            ]);
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
 *     summary="Liste des paiements éffectués sur le site lors des reservations",
 *     description="Liste des paiements éffectués sur le site lors des reservations.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des paiements éffectués sur le site lors des reservations",
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
    
        return response()->json([
            'message' => 'Liste de tous les paiements effectués sur le site',
            'data' => $payments,
        ],200);
    }


}    
