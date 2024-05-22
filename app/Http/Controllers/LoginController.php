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
use App\Models\User_right;
use App\Models\Right;
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
use App\Mail\NotificationEmail;
class LoginController extends Controller
{
    
/**
 * @OA\Post(
 *     path="/api/users/login",
 *     summary="make authentification",
 *     tags={"Authentication"},
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="email", type="string", example="a@gmail.com"),
 *             @OA\Property(property="password", type="string", example="P@$$w0rd")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="connected successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="token_type", type="string", example="Bearer"),
 *             @OA\Property(property="role", type="array", @OA\Items(type="string")),
 *             @OA\Property(property="access_token", type="string", example="your-access-token")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Invalid credentials"
 *     )
 * )
 */

/**
* @OA\Post(
*     path="/api/users/login",
*     summary="make authentification",
*     tags={"Authentication"},
*      @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"name"},
   *             @OA\Property(property="email", type="string", example ="a@gmail.com"),
   *             @OA\Property(property="password", type="string", example ="P@$$w0rd")
   *         )
   *     ),
*     @OA\Response(
*         response=201,
*         description=" connected successfully"
*     ),
*     @OA\Response(
*         response=201,
*         description="Invalid credentials"
*     )
* )
*/


public function login(Request $request){
    try{
        $request->validate([
          'email' => 'required|email',
          'password' => 'required',
        ]);
  
        $user = User::where('email', $request->email)->first();
        if($user !=null){
          if (Hash::check($request->password, $user->password)) {
              $token = $user->createToken('auth_token')->plainTextToken;  
              $codes = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
              if($user->code !== null)  {
                  $user->code = $codes;
                  $user->save();
              }
  
              $mail = [
                  'title' => 'Entrez le code suivant pour finaliser votre authentification.',
                  'body' => $codes
              ];

              $userRights = User_right::where('user_id', $user->id)->get();

              $rightsDetails = [];
              
              foreach ($userRights as $userRight) {
                  $right = Right::find($userRight->right_id);
                  
                  if ($right) {
                      $rightsDetails[] = [
                          'right_id' => $right->id,
                          'right_name' => $right->name,
                      ];
                  }
              }
              
            //   Mail::to($request->email)->send(new ConfirmationLoginEmail($mail) );
              unset($user->code);
              return response()->json([
                  'user' => $user,
                  'role_actif' => $user->getRoleNames(),
                  'appartement_id' => $token,
                  'user_role' =>$rightsDetails
              ]);
          } else {
              return response()->json(['error' => 'Mot de passe invalide.'], 201);
        }
  
  
      }else {
          return response()->json(['error' => 'Adresse email invalide.'], 201);
      }
  
     } catch(Exception $e) {    
      return response()->json($e->getMessage());
      }
}

/**
 * @OA\Get(
 *     path="/api/user",
 *     summary="Check authentication status",
 *     description="Check if the user is authenticated and retrieve user data and role",
 *     tags={"Authentication"},
 *  security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="role", type="array", @OA\Items(type="string"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Unauthorized"
 *     )
 * )
 */
public function checkAuth(Request $request){

    try{
        $userRights = User_right::where('user_id', Auth::user()->id)->get();

        $rightsDetails = [];
        
        foreach ($userRights as $userRight) {
            $right = Right::find($userRight->right_id);
            
            if ($right) {
                $rightsDetails[] = [
                    'right_id' => $right->id,
                    'right_name' => $right->name,
                ];
            }
        }
        return response()->json([
            'data' => Auth::user(),
            'role_actif'=>Auth::user()->getRoleNames(),
            'user_roles'=>$rightsDetails
        ]);

    } catch (Exception $e) {
        return response()->json($e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/users/verification_code",
 *     tags={"Authentication"},
 *     summary="Vérification du code de vérification",
 *     description="Vérifie le code de vérification envoyé par l'utilisateur.",
 *     requestBody={
 *         "required": true,
 *         "content": {
 *             "application/json": {
 *                 "schema": {
 *                     "type": "object",
 *                     "properties": {
 *                         "code": {
 *                             "type": "string",
 *                             "description": "Le code de vérification à vérifier."
 *                         }
 *                     },
 *                     "required": "code"
 *                 }
 *             }
 *         }
 *     },
 *     @OA\Response(
 *         response="201",
 *         description="Échec de la vérification",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=201,
 *                 description="Le code d'état de la réponse."
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Check failed",
 *                 description="Le message indiquant que la vérification a échoué."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response="500",
 *         description="Erreur interne du serveur",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=500,
 *                 description="Le code d'état de la réponse."
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Le message d'erreur détaillé."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response="default",
 *         description="Réponse par défaut pour les autres cas",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=201,
 *                 description="Le code d'état de la réponse."
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Verification passed",
 *                 description="Le message indiquant que la vérification a réussi."
 *             ),
 *             @OA\Property(
 *                 property="verification",
 *                 type="string",
 *                 description="Le code de vérification vérifié."
 *             )
 *         )
 *     )
 * )
 */
public function verification_code(Request $request)
{
    try {
        $verification = $request->code;
        $code = User::where('code', $verification)->first();

        if ($code !== null) {
            return response()->json([
                'status_code' => 201,
                'message' => 'Verification passed',
                'verification' => $verification
            ]);
        }

        return response()->json([
            'status_code' => 201,
            'message' => 'Check failed',
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'message' => $e->getMessage(),
        ]);
    }
}


/**
 * @OA\Post(
 *     path="/api/users/new_code/{id}",
 *     summary="Generate a new code for user",
 *     tags={"Authentication"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Code sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Code sent successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="This id does not exist",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="This id does not exist")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Internal Server Error")
 *         )
 *     )
 * )
 */
public function new_code($id) {
    try {
        if($id !== null) {
            $user = User::find($id);
            $email = $user->email;
            $codes = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            if($user->code !== null) {
                $user->code = $codes;
                $user->save();
            }
            $mail = [
                'title' => 'Help us protect your account',
                'body' => $user->code
            ];
            Mail::to($email)->send(new ConfirmationLoginEmail($mail) );
            
            return response()->json([
                'status_code' => 200,
                'message' => 'Code sent successfully',
            ]);
        } 
        return response()->json([
            'status_code' => 404,
            'message' => 'This id does not exist'
        ]);

    } catch (Exception $e) {
        return response()->json(['message' => 'Internal Server Error'], 500);
    }
}

 /**
 * @OA\Post(
 *     path="/api/users/password_recovery_start_step",
 *     summary="Start password recovery process",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="email",
 *                     type="string",
 *                     description="User's email address",
 *                     example="user@example.com"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Email sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Email sent successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Email not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="Email not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Internal Server Error")
 *         )
 *     )
 * )
 */

public function password_recovery_start_step(Request $request){
    try{

        $request->validate([
            'email' => 'required',
        ]);
        $email = $request->email;
        if(User::where('email',$email)->exists()){
            $user = User::where('email',$email)->first();
            $mail = [
                'title' => 'Reinitialisation de mot de passe',
                'body' => 'Clique sur ce lien : https://quotidishop.com/page/account/change-password pour reinitialiser votre mot de passe'
            ];

            
            Mail::to($request->email)->send(new NotificationEmail($mail) );
        
            return response()->json([
                'status_code' => 200,
                'message' => "Email sent successfully"
             ]);
        }else{
            return response()->json([
                'status_code' => 404,
                'message' => "Email not found"
             ]);
        }

    } catch(Exception $e) {
        return response()->json($e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/users/password_recovery_end_step",
 *     summary="End password recovery process",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="email",
 *                     type="string",
 *                     description="User's email address",
 *                     example="user@example.com"
 *                 ),
 *                 @OA\Property(
 *                     property="new_password",
 *                     type="string",
 *                     description="New password for the user",
 *                     example="new_password123"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password changed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=200),
 *             @OA\Property(property="message", type="string", example="Password changed successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Email not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=404),
 *             @OA\Property(property="message", type="string", example="Email not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Internal Server Error")
 *         )
 *     )
 * )
 */

public function password_recovery_end_step(Request $request){
    try {

        $request->validate([
            'email' => 'required',
            'new_password' => 'required'
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();
        if($user){
            $user->update(['password' => Hash::make($request->new_password)]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Password changed successfully'
            ]);
        }else {
            return response()->json([
                'status_code' => 200,
                'message' => 'Email not found'
                ]);
        }

          } catch(Exception $e) {
            return response()->json($e->getMessage());
            }
}

}
