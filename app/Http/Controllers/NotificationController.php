<?php

namespace App\Http\Controllers;

use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
   /**
     * @OA\Get(
     *     path="/api/notifications/index",
     *     summary="Get all notification in the site",
     *     tags={"Notification"},
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
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "name"},
 *             @OA\Property(property="user_id", type="integer", example="1", description="ID de l'utilisateur"),
 *             @OA\Property(property="name", type="string", example="Notification Example", description="Nom de la notification")
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
     * Display the specified resource.
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        //
    }


/**
 * @OA\Get(
 *     path="/api/notifications/users",
 *     summary="Récupérer la liste de notifications pour un utilisateur connecté",
 *     tags={"Notification"},
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
        $userId = 11;
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
}
