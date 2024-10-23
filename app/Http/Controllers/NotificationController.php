<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\Notification;
use App\Models\Right;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\User_right;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class NotificationController extends Controller
{


    /**
 * @OA\Get(
 *     path="/api/notifications/getUserForNotification",
 *     tags={"Notification"},
 *     summary="Récupère la liste des utilisateurs pour les notifications",
 *     description="Retourne une liste des utilisateurs non supprimés (is_deleted = false).",
 *     operationId="getUserForNotification",
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des utilisateurs.",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="lastname", type="string", example="Doe"),
 *                 @OA\Property(property="firstname", type="string", example="John")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erreur.")
 *         )
 *     )
 * )
 */

    public function getUserForNotification(){
        try {
            $users = User::where('is_deleted', false)
            ->get();

            $formattedUsers = [];
            foreach ($users as $user) {
                $formattedUsers[] = [
                    'id' => $user->id,
                    'lastname' => $user->lastname,
                    'firstname' => $user->firstname,
                ];
            }
            
            return (new ServiceController())->apiResponse(200, $formattedUsers, 'Liste des utilisateurs.');
        } catch (\Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }
   /**
     * @OA\Get(
     *     path="/api/notifications/index",
     *     summary="Get all notification in the site",
     *     tags={"Notification"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of the notification"
     *     )
     * )
     */
    public function index()
    {
        $notifications = Notification::all();
        $totalNotifications = $notifications->count();

        foreach($notifications as $notification){
            $notification->username = User::whereId($notification->user_id)->first()->firstname;
        }

        return response()->json([
            'notifications' => $notifications,
            'total' => $totalNotifications
        ]);
    }

/**
 * @OA\Post(
 *     path="/api/notifications/store",
 *     summary="Ajouter une notification à un utilisateur,ce dernier verra dans sa liste de notification une fois connecté",
 *     tags={"Notification"},
 * security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "name"},
 *             @OA\Property(property="user_id", type="integer", example="1", description="ID de l'utilisateur"),
 *  @OA\Property(property="object", type="string", example="objet", description="Nom de la notification"),
 *             @OA\Property(property="name", type="string", example="Notification Example", description="Nom de la notification"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Notification ajoutée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Notification 'Notification Example' ajoutée à l'utilisateur 'John Doe' avec succès"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *         )
 *     )
 * )
 */

 public function storeNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'name' => 'required',
            'object' => 'required',
        ]);

        $this->store(User::whereId($request->user_id)->first()->email,$request->name,$request->object,2);

        $name = User::find($request->user_id)->firstname;
        return (new ServiceController())->apiResponse(200, [],"Notification envoyée à $name avec succès ");
    }


    public function store($email,$name,$object,$is_send_by_mail=0)
    {
        //2 => notif et mail
        //0 => notif seul
        //1 => mail seul

        $notificationName = $name;
        $notificationObject = $object;
        if($is_send_by_mail==0 || $is_send_by_mail==2){
            $notification = new Notification();
            $notification->user_id = User::whereEmail($email)->first()->id;
            $notification->name = $notificationName;
            $notification->object = $notificationObject;
            $notification->save();
        }

        if($is_send_by_mail==1 || $is_send_by_mail==2){
            $mail = [
                'title' => $notificationObject,
                'body' =>$notificationName
            ];
            Mail::to($email)->send(new NotificationEmailwithoutfile($mail));



// Mail::to($to)->send(new YourMailable());

        }

        return response()->json([
            'message' => "Notification '$notificationName' ajouté avec succès",
            'notification' => $notification,
        ], 200);
    }


    public function storeAndSendFileEmail($email,$name,$object,$filePaths)
    {


        $notificationName = $name;
        $notificationObject = $object;

            $notification = new Notification([
                'name' => $notificationName,
                'user_id' =>  User::whereEmail($email)->first()->id,
                'object' => $notificationObject
            ]);
            $notification->save();


            $mail = [
                'title' => $notificationObject,
                'body' =>$notificationName
            ];
            Mail::to($email)->send(new NotificationEmail($mail,$filePaths));

        return response()->json([
            'message' => "Notification '$notificationName' ajouté avec succès",
            'notification' => $notification,
        ], 200);
    }

  

/**
 * @OA\Get(
 *     path="/api/users/notifications",
 *     summary="Récupérer la liste de notifications pour un utilisateur connecté",
 *     tags={"Notification"},
 * security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des notifications avec succès pour l'utilisateur",
 *         @OA\JsonContent(
 *             type="object",
 *         )
 *     )
 * )
 */
    public function getUserNotifications()
    {
        //$userID = auth()->user()->id;
        $userId = Auth::user()->id;
        $notification = Notification::where('user_id', $userId)->get();
        if (!$notification ) {
            return response()->json(['error' => 'Notification non trouvée non trouvé'], 404);
        }

        $totalUserNotifications = $notification->count();

        return response()->json([
            'user_notifications' => $notification,
            'total_user_notifications' => $totalUserNotifications
        ]);
    }

      /**
 * Notify users with specified roles.
 *
 * @OA\Post(
 *     path="/api/notifications/notifyUserHaveRoles/{mode}",
 *     tags={"Notification"},
 *  security={{"bearerAuth": {}}},
 *   @OA\Parameter(
     *         name="mode",
     *         in="path",
     *         required=true,
     *         description="Mode de transmission(avec email et mail ou l'un des deux)",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="roleIds",
 *                     type="array",
 *                     @OA\Items(
 *                         type="integer"
 *                     ),
 *                     description="Array of role IDs"
 *                 ),
 *                 @OA\Property(
 *                     property="object",
 *                     type="string",
 *                     description="Object of the notification"
 *                 ),
 *                 @OA\Property(
 *                     property="content",
 *                     type="string",
 *                     description="Content of the notification"
 *                 ),
 *                 required={"roleIds", "object", "content"}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Message marked as unread successfully"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role not found",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Role not found"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Server error"
 *             )
 *         )
 *     )
 * )
 */
public function notifyUserHaveRoles(Request $request,$mode){
    try {

            $request->validate([
                    'roleIds' => 'required|array',
                    'object' => 'required',
                    'content' => 'required'
            ]);

            foreach($request->roleIds as $roleId){

                $roleExist = Role::whereId($roleId)->first();

                if(!$roleExist){
                    return (new ServiceController())->apiResponse(404,[],'Role non trouvé');
                }

                $role = Right::where('id', $roleId)->first();

                $userR = User_right::where('right_id', $role->id)->get();

                // return $userR;
                foreach($userR as $user){
                    if(!User::find($user->user_id)){
                        return (new ServiceController())->apiResponse(404,[],"Utilisateur non trouvé {$user->user_id} ");
                    }
                }

            }

            foreach($request->roleIds as $roleId){

                $role = Right::where('id', $roleId)->first();

                $userR = User_right::where('right_id', $role->id)->get();
                $emails = [];

                foreach($userR as $user){
                    $emails[] = User::whereId($user->user_id)->first()->email;
                }

            }

            (new NotificationController())->store($emails, $request->content,$request->object,$mode);

        return (new ServiceController())->apiResponse(200, [],'Notification envoyé avec succès');

    } catch(Exception $e) {
         return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}


/**
* @OA\Post(
*     path="/api/notifications/notifyUsers/{mode}",
*     summary="Notify users",
*     description="Send a notification to multiple users",
*     tags={"Notification"},
*    @OA\Parameter(
 *         name="mode",
 *         in="path",
 *         required=true,
 *         description="Mode de transmission(avec email et mail ou l'un des deux)",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
*     security={{"bearerAuth": {}}},
*     @OA\RequestBody(
*         required=true,
*         @OA\MediaType(
*             mediaType="application/json",
*             @OA\Schema(
*                 type="object",
*                 required={"userIds", "object", "content"},
*                 @OA\Property(
*                     property="userIds",
*                     type="array",
*                     @OA\Items(type="integer"),
*                     description="List of user IDs to notify"
*                 ),
*                 @OA\Property(
*                     property="object",
*                     type="string",
*                     description="Notification object"
*                 ),
*                 @OA\Property(
*                     property="content",
*                     type="string",
*                     description="Notification content"
*                 )
*             )
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="Notification sent successfully",
*         @OA\JsonContent(
*             @OA\Property(
*                 property="message",
*                 type="string",
*                 example="Message marqué comme lu avec succès"
*             )
*         )
*     ),
*     @OA\Response(
*         response=404,
*         description="User not found",
*         @OA\JsonContent(
*             @OA\Property(
*                 property="message",
*                 type="string",
*                 example="Utilisateur non trouvé"
*             )
*         )
*     ),
*     @OA\Response(
*         response=500,
*         description="Internal server error",
*         @OA\JsonContent(
*             @OA\Property(
*                 property="message",
*                 type="string",
*                 example="Error message"
*             )
*         )
*     )
* )
*/

public function notifyUsers(Request $request, $mode){
    try {

        $request->validate([
            'userIds' => 'required|array',
            'object' => 'required',
            'content' => 'required'
    ]);

    foreach($request->userIds as $userId){
        if(!User::find($userId)){
            return (new ServiceController())->apiResponse(404,[$userId],'Utilisateur non trouvé');
        }
    }

    $emails = [];
    foreach($request->userIds as $userId){
        $emails[] = User::whereId($userId)->first()->email;
    }

    (new NotificationController())->store($emails, $request->content,$request->object,$mode);
    return (new ServiceController())->apiResponse(200, [],'Notification envoyé avec succès');
       
    } catch(Exception $e) {
         return (new ServiceController())->apiResponse(500,[],$e->getMessage());
    }
}
}

