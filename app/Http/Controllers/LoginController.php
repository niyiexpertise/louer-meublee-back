<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
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
use Illuminate\Support\Str;
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
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

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
 *         response=200,
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
*         response=200,
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
            if($user->is_blocked){
                return response()->json([
                    'error' => 'Vous avez été bloqué. Veuillez contacter l\'administrateur pour plus de détails.'
                ], 200);
                
            } 
            if($user->is_deleted){
                return response()->json([
                    'error' => 'Veuillez contacter l\'administrateur pour plus de détails car vous avez été supprimé.'
                ], 200);
                
            } 
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('auth_token')->plainTextToken;
                $codes = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $user->code = $codes;
                $user->save();


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

                // Mail::to($request->email)->send(new ConfirmationLoginEmail($mail) );

                try {
                    $user->is_double_authentification = 0;
                    $user->save();
                    Mail::to($request->email)->send(new ConfirmationLoginEmail($mail) );
                } catch (\Exception $e) {

                }

                // dd('salut');
              unset($user->code);
              return response()->json([
                  'user' => $user,
                  'role_actif' => $user->getRoleNames(),
                  'appartement_id' => $token,
                  'user_role' =>$rightsDetails
              ]);
          } else {
              return response()->json(['error' => 'Mot de passe invalide.'], 200);
        }


      }else {
          return response()->json(['error' => 'Adresse email invalide.'], 200);
      }

     } catch(\Exception $e) {
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
 *         response=400,
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
            'user_roles'=>$rightsDetails,
            'permissions' => (new AuthController)->getUserPerms(Auth::user()->id)->original['data']
        ]);

    } catch (\Exception $e) {
        return response()->json($e->getMessage());
    }
}

/**
 * @OA\Post(
 *     path="/api/users/verification_code",
 *     tags={"Authentication"},
 *     security={{"bearerAuth": {}}},
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
 *         response="200",
 *         description="Échec de la vérification",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=200,
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
 *                 example=200,
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
        // $code = User::where('code', $verification)->first();
       

        if(!(User::whereId(Auth::user()->id)->first())){
            return (new ServiceController())->apiResponse(404,[], 'Utilisateur non trouvé');
        }

        if(User::whereId(Auth::user()->id)->first()->code !=$verification ){
            return [User::whereId(Auth::user()->id)->first()->code,$verification]; 
            return (new ServiceController())->apiResponse(404,[], "Ce code n'appartient pas à l'utilisateur connecté");
        }

        $code = User::where([
            ['id', Auth::user()->id],
            ['code', $verification]
        ])->first();


    //     if(!$code)
    //   {
    //         return (new ServiceController())->apiResponse(404,[], 'Utilisateur ayant ce code non trouvé');
    //     }
        $code->code=0;  
        $code->is_double_authentification = true;
        $code->save();
        if ($code !== null) {
            return response()->json([
                'status_code' => 200,
                'message' => 'Verification passed',
            ]);
        }

        return response()->json([
            'status_code' => 200,
            'message' => 'Check failed',
        ]);

    } catch (\Exception $e) {
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

    } catch (\Exception $e) {
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
        $user = User::where('email', $request->email)->first();
        if($user){

            if($user->is_blocked == true){
                return (new ServiceController())->apiResponse(404,[], "Désolé mais vous êtes bloqué.");
            }

            if($user->is_deleted == true){
                return (new ServiceController())->apiResponse(404,[], "Désolé mais vous êtes supprimé.");
            }

            $exists = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->exists();

            if($exists){
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            // URL du frontend pour le formulaire de réinitialisation de mot de passe
            $frontendUrl = 'https://mon-frontend.com/reset-password';

            // Génère le lien avec le token et l'email
            $resetLink = $frontendUrl . '?token=' . $token . '&email=' . urlencode($request->email);


            $mail = [
                'title' => 'Réinitialisation de mot de passe',
                'body' => "Cliquez sur ce lien : {$resetLink} et suivez les étapes pour réinitialiser votre mot de passe."
            ];

            dispatch( new SendRegistrationEmail($request->email, $mail['body'], $mail['title'], 1));

            return (new ServiceController())->apiResponse(200,[], "Email envoyé avec succès");

        }else{
            return (new ServiceController())->apiResponse(404,[], "Email non trouvé");
        }

    } catch(\Exception $e) {
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
 *               @OA\Property(
 *                     property="token",
 *                     type="string",
 *                     description="token généré pour la modification du mot de passe",
 *                     example="osdf156s4d4ez6z27c2We6zg2"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
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
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'token' => 'required|string',
        ]);

        $passwordReset = DB::table('password_reset_tokens')->where('email', $request->email)->where('token', $request->token)->first();

        if (!$passwordReset) {
            return (new ServiceController())->apiResponse(404, [],"Email ou Token invalide");
        }

        $expirationTime = config('auth.passwords.users.expire');
        if (Carbon::parse($passwordReset->created_at)->addMinutes($expirationTime)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return (new ServiceController())->apiResponse(404, [],"Ce lien de réinitialisation du mot de passe est expiré.");
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return (new ServiceController())->apiResponse(404, [],"Utilisateur non trouvé.");
        }

        $user->is_double_authentification = 0;
        $user->password = bcrypt($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $user = User::whereId($user->id)->first();

        $deconnectes = PersonalAccessToken::where('tokenable_id', $user->id)
            ->where('tokenable_type', 'App\Models\User')->get();
                    foreach($deconnectes as $deconnecte){
                        $deconnecte->delete();
            }

        return (new ServiceController())->apiResponse(404, [],"Mot de passe modifié avec succès.");

          } catch(\Exception $e) {
            return response()->json($e->getMessage());
        }
}

    public function returnAuthCommission(){
        $user = Auth::user();
        if(!$user){
            return (new ServiceController())->apiResponse(403, [], "UNAUTHENTIFICATED");
        }

        $commission = $user->commission->valeur ?? "null";

        return $commission;
    }

}
