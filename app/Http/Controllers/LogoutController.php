<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
use App\Models\Portfeuille;
use App\Models\Portfeuille_transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationLoginEmail;
use Laravel\Sanctum\PersonalAccessToken;
class LogoutController extends Controller
{
   /**
 * @OA\Post(
 *     path="/api/users/logout",
 *     tags={"Deconnexion"},
 *     summary="Logout the user",
 *     operationId="logout",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully logged out",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="You are disconnected")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *     )
 * )
 */
public function logout(Request $request) {
    try {
        
        if (Auth::check()) {
            
            $accessToken = $request->bearerToken();

            
            if ($accessToken) {
                $token = PersonalAccessToken::findToken($accessToken);

                if ($token) {
                    
                    $token->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'You are disconnected'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid token'
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Token not provided'
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 200);
        }
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


}
