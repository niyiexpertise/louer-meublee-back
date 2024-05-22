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
use App\Models\Right;
use App\Models\User_right;
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
use App\Models\User_language;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmationLoginEmail;
use App\Mail\NotificationEmailwithoutfile;
class InscriptionController extends Controller
{
   /**
 * @OA\Post(
 *     path="/api/users/register",
 *     tags={"Inscription"},
 *     summary="Enregistrer un nouvel utilisateur",
 *     description="Enregistre un nouvel utilisateur avec les informations fournies",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="nom", type="string", example="Doe", description="Nom de l'utilisateur"),
 *                 @OA\Property(property="prenom", type="string", example="John", description="Prénom de l'utilisateur"),
 *                 @OA\Property(property="password", type="string", format="password", example="Bagdadi2000!", description="Mot de passe (min : 8 caractères, une majuscule, un chiffre, un caractère spécial)"),
 *                 @OA\Property(property="code_pays", type="string", example="FR", description="Code du pays"),
 *                 @OA\Property(property="telephone", type="string", example="1234567890", description="Numéro de téléphone"),
 *                 @OA\Property(property="email", type="string", format="email", example="john.doe@gmail.com", description="Adresse e-mail de l'utilisateur"),
 *                 @OA\Property(property="pays", type="string", example="France", description="Pays de l'utilisateur"),
 *                 @OA\Property(property="identity_profil", type="file", format="binary", description="Image de profil d'identité (JPEG, PNG, JPG, GIF, taille max : 2048)"),
 *                 @OA\Property(property="ville", type="string", example="Paris", description="Ville de l'utilisateur"),
 *                 @OA\Property(property="addresse", type="string", example="123 Rue de la Paix", description="Adresse de l'utilisateur"),
 *                 @OA\Property(property="sexe", type="string", example="Masculin", description="Sexe de l'utilisateur"),
 *                 @OA\Property(property="postal_code", type="string", example="75001", description="Code postal de l'utilisateur"),
 *                 @OA\Property(
 *                     property="language_id[]",
 *                     type="array",
 *                     @OA\Items(type="integer", description="ID de la langue préférée de l'utilisateur")
 *                 ),
 *                 @OA\Property(property="password_confirmation", type="string", format="password", example="Bagdadi2000!", description="Confirmation du mot de passe (doit correspondre au mot de passe)")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Utilisateur enregistré avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Utilisateur enregistré avec succès"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="object", additionalProperties={"type": "string"})
 *         )
 *     )
 * )
 */



 public function register(Request $request)
 {
     
     
     $validator = Validator::make($request->all(), [
         'nom' => 'required|string',
         'prenom' => 'required|string',
         'password' => [
             'required',
             'string',
             'min:8',
             'confirmed',
             'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
         ],
         'code_pays' => 'required|string',
         'telephone' => 'required|String|numeric|unique:users',
         'email' => 'required|email|unique:users',
         'pays' => 'required|string',
         'identity_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         'ville' => 'required|string',
         'addresse' => 'required|string',
         'sexe' => 'required|string',
         'password_confirmation' => 'required|string',
         
     ]);

     if ($validator->fails()) {
        // return response()->json(['message' => 'User registered successfully'], 201);
         return response()->json(['error' => $validator->errors()], 200);
     }
     
     if ($request->hasFile('identity_profil')) {
     $identity_profil_name = uniqid() . '.' . $request->file('identity_profil')->getClientOriginalExtension();
     $identity_profil_path = $request->file('identity_profil')->move(public_path('image/photo_profil'), $identity_profil_name);
     $base_url = url('/');
     $identity_profil_url = $base_url . '/image/photo_profil/' . $identity_profil_name;
     }
     $user = new User([
         'lastname' => strtoupper($request->nom),
         'firstname' => $request->prenom,
         'password' => bcrypt($request->password),
         'telephone' => $request->telephone,
         'code_pays' => $request->code_pays,
         'email' => $request->email,
         'country' => $request->pays,
         'city' => $request->ville,
         'address' => $request->addresse,
         'sexe' => $request->sexe,
         'postal_code' => $request->postal_code,
         
     ]);

     $user->save();
     $right = Right::where('name','traveler')->first();
     $user->assignRole('traveler');
     $user_right = new User_right();
     $user_right->user_id = $user->id;
     $user_right->right_id = $right->id;
     $user_right->save();

    /* $userLanguages =$request->language_id;
     if (!empty($request->language_id)) {
        foreach ($userLanguages as $language_id) {
            $userLanguage = new User_language([
               'user_id' => $user->id,
               'language_id' => $language_id,
                 ]);
   
           $userLanguage->save();
               }
    } */

     $created_at = $user->created_at;
     $date_creation = Carbon::parse($created_at)->isoFormat('D MMMM YYYY [à] HH[h]mm');
     $message_notification = "Compte créé avec succès le " . $date_creation;

        $notification = new Notification([
         'name' => $message_notification,
         'user_id' =>$user->id,
         
     ]);
     $notification->save();
     $mail = [
        'title' => 'Inscription',
        'body' => "Compte créé avec succès le ". $date_creation
    ];
    
    Mail::to($request->email)->send(new NotificationEmailwithoutfile($mail) );

     $portfeuille= new Portfeuille([
         'solde' =>0,
         'user_id' =>$user->id,
         
     ]);
     $portfeuille->save();

     return response()->json(['message' => 'User registered successfully','users'=>$user], 201);
 }

}
