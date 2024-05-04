<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\NotificationEmail;
use App\Mail\NotificationEmailwithoutfile;

class NotificationController extends Controller
{
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


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $notificationName = $request->name;
        $userId=$request->user_id;
        $notification = new Notification([
            'name' => $notificationName,
            'user_id' => $userId,
        ]);

        $notification->save();
        $user = User::findOrFail($userId);

        $mail_to_traveler= [
            'title' => 'Notification',
            'body' =>$notificationName
                 ];
    
        Mail::to($user->email)->send(new NotificationEmailwithoutfile($mail_to_traveler));

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
        $userId  = auth()->user()->id;
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
 * @OA\Get(
 *     path="/api/notifications/unread",
 *     summary="Obtenir les notifications non lues pour un utilisateur connecté",
 *     tags={"Notification"},
 *     security={{"bearerAuth": {}}},  
 *     @OA\Response(
 *         response=200,
 *         description="Notifications non lues pour l'utilisateur connecté",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="unread_notifications",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Nouvelle notification"),
 *                     @OA\Property(property="is_read", type="boolean", example=false),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-01T10:00:00Z"),
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


    public function getUnreadNotifications()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non connecté.'], Response::HTTP_UNAUTHORIZED);
            }

            $unreadNotifications = Notification::where('user_id', $user->id)
                                            ->where('is_read', false)
                                            ->get();

            return response()->json(['unread_notifications' => $unreadNotifications], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/notifications/read",
     *     summary="Obtenir les notifications lues pour un utilisateur connecté",
     *     tags={"Notification"},
     *     security={{"bearerAuth": {}}},  
     *     @OA\Response(
     *         response=200,
     *         description="Notifications lues pour l'utilisateur connecté",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="read_notifications",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="is_read", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
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


    public function getReadNotifications()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non connecté.'], Response::HTTP_UNAUTHORIZED);
            }

            $readNotifications = Notification::where('user_id', $user->id)
                                            ->where('is_read', true)
                                            ->get();

            return response()->json(['read_notifications' => $readNotifications], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/notifications/{id}/markread",
     *     summary="Marquer une notification comme lue",
     *     tags={"Notification"},
     *     security={{"bearerAuth": {}}},  
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la notification à marquer comme lue",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marquée comme lue",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification marquée comme lue."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Notification non trouvée."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Notification déjà lue",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Notification déjà lue."),
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

    public function markNotificationAsRead($notificationId)
    {
        try {
            $notification = Notification::find($notificationId);

            if (!$notification) {
                return response()->json(['error' => 'Notification non trouvée.'], Response::HTTP_NOT_FOUND);
            }

            if ($notification->is_read) {
                return response()->json(['error' => 'Notification déjà lue.'], Response::HTTP_CONFLICT);
            }

            Notification::whereId($notificationId)->update(['is_read' => true]);

            return response()->json(['message' => 'Notification marquée comme lue.'], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
