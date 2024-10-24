<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\TicketChat;
use App\Models\TicketChatMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketChatController extends Controller
{

    protected $permission;

    public function __construct()
    {
        $this->permission = 'aaa';
    }


  /**
 * @OA\Post(
 *     path="/api/ticketChat/createTicketOrMessage",
 *     tags={"Ticket Chat"},
 *     summary="Créer un ticket ou envoyer un message",
 *     description="Cette fonction permet de créer un nouveau ticket ou d'envoyer un message dans un ticket existant.",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="ticket_chat_id", type="integer", example=1, description="ID du ticket de discussion existant"),
 *                 @OA\Property(property="title", type="string", example="Problème de connexion", description="Titre du ticket"),
 *                 @OA\Property(property="description", type="string", example="Je ne peux pas me connecter à mon compte.", description="Description du ticket"),
 *                 @OA\Property(property="content", type="string", example="Veuillez m'aider avec ce problème.", description="Contenu du message"),
 *                 @OA\Property(property="files", type="array", @OA\Items(type="string", format="binary"), description="Fichiers à joindre")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Ticket créé ou message envoyé avec succès"),
 *     @OA\Response(response=404, description="Erreur de validation ou ticket non trouvé"),
 *     @OA\Response(response=500, description="Erreur interne du serveur")
 * )
 */

    public function createTicketOrMessage(Request $request)
    {
        // DB::beginTransaction();
        try {
            $ticketChatId = $request->ticket_chat_id;

            if (!$ticketChatId) {

                if(!$request->title){
                    return (new ServiceController())->apiResponse(404, [], "Veuillez donner un titre à votre ticket");
                }

                if(!$request->description){
                    return (new ServiceController())->apiResponse(404, [], "Veuillez donner une description à votre ticket");
                }

                $ticketChat = new TicketChat();
                $ticketChat->reference = '#'.(new ChatController())->generateRandomAlphaNumeric(8,$ticketChat,'reference');
                $ticketChat->user_id = Auth::id();
                $ticketChat->title = $request->title;
                $ticketChat->description = $request->description;
                $ticketChat->save();

                $messageContent = $ticketChat->title;
                (new TicketChatMessageController())->createTicketChatMessage($ticketChat->id, $messageContent, Auth::id(), $request->file('files'));
                 return (new ServiceController())->apiResponse(200, [], 'Ticket créé avec succès');
                $ticketChatId = $ticketChat->id;
            } else {

                if(!$request->content){
                    return (new ServiceController())->apiResponse(404, [], "Message vide, veuillez saisir quelque chose");
                }

                $ticketChat = TicketChat::whereId($ticketChatId)->first();

                if(!$ticketChat){
                    return (new ServiceController())->apiResponse(404, [], 'Ticket de discussion non trouvé');
                }

                if($ticketChat->is_open == false){
                    return (new ServiceController())->apiResponse(404, [], 'Ticket de discussion fermé');
                   }

                   $supportTechniques = (new PermissionController())->getEmailsByPermissionName($this->permission);

                   if(Auth::id() !=  $ticketChat->user_id && !in_array(Auth::user()->email, $supportTechniques)){
                        return (new ServiceController())->apiResponse(404, [], "Vous n'êtes pas autorisé à prendre part à cette discussion");
                   }

                (new TicketChatMessageController())->createTicketChatMessage($ticketChatId, $request->content, Auth::id(), $request->file('files'));
                return (new ServiceController())->apiResponse(200, [], 'Message créé avec succès');
            }

            // DB::commit();
           
        } catch (Exception $e) {
            // DB::rollBack();
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    /**
 * @OA\Get(
 *     path="/api/ticketChat/getuserTicketChat",
 *     tags={"Ticket Chat"},
 *     security={{"bearerAuth": {}}},
 *     summary="Récupérer les tickets de discussion de l'utilisateur connecté",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des tickets de discussion d'une personne connectée"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la récupération des tickets"
 *     )
 * )
 */
    public function getuserTicketChat(){
        try{
            $ticketChats = TicketChat::whereUserId( Auth::id())->where('is_deleted',0)->get();

            return (new ServiceController())->apiResponse(200, $ticketChats, "Liste des tickets de discussion d'une personne connectée");

        }catch(Exception $e){
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
 * @OA\Get(
 *     path="/api/ticketChat/getTicketChat",
 *     tags={"Ticket Chat"},
 *     security={{"bearerAuth": {}}},
 *     summary="Récupérer tous les tickets de discussion",
 *     @OA\Response(
 *         response=200,
 *         description="Liste des tickets de discussion"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Non autorisé à afficher la liste des préoccupations"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la récupération des tickets"
 *     )
 * )
 */
    public function getTicketChat(){
        try{

            $supportTechniques = (new PermissionController())->getEmailsByPermissionName($this->permission);

            if(!in_array(Auth::user()->email, $supportTechniques)){
                return (new ServiceController())->apiResponse(404, [], "Vous n'êtes pas autorisé à afficher la liste des préoccupations faites sur la plateforme");
            }

            $ticketChats = TicketChat::where('is_deleted',0)->get();

            return (new ServiceController())->apiResponse(200, $ticketChats, "Liste des tickets de discussion d'une personne connectée");

        }catch(Exception $e){
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
 * @OA\Get(
 *     path="/api/ticketChat/getTicketChatMessageByTicketChat/{ticketChatId}",
 *     tags={"Ticket Chat"},
 *     security={{"bearerAuth": {}}},
 *     summary="Récupérer les messages d'un ticket de discussion",
 *     @OA\Parameter(
 *         name="ticketChatId",
 *         in="path",
 *         required=true,
 *         description="ID du ticket de discussion",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des messages d'un ticket de discussion"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ticket de discussion non trouvé ou non autorisé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la récupération des messages"
 *     )
 * )
 */
    public function getTicketChatMessageByTicketChat($ticketChatId){

        try{

            $ticketChat = TicketChat::whereId($ticketChatId)->first();

            if(!$ticketChat){
                return (new ServiceController())->apiResponse(404, [], 'Ticket de discussion non trouvé');
            }

            $supportTechniques = (new PermissionController())->getEmailsByPermissionName($this->permission);

            if(Auth::id() !=  $ticketChat->user_id && in_array(Auth::user()->email, $supportTechniques)){
                 return (new ServiceController())->apiResponse(404, [], "Vous n'êtes pas autorisé à voir les messages de cette discussion");
            }

            $ticketChatMessages = TicketChatMessage::whereTicketChatId($ticketChatId)->get();
            return (new ServiceController())->apiResponse(200, $ticketChatMessages, "Liste des messages d'un ticket de discussion");

        }catch(Exception $e){
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }

    /**
 * @OA\Post(
 *     path="/api/ticketChat/markMessageAsRead",
 *     tags={"Ticket Chat"},
 *     security={{"bearerAuth": {}}},
 *     summary="Marquer les messages comme lus",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"messageIds"},
 *             @OA\Property(property="messageIds", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message marqué comme lu avec succès"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Message non trouvé ou non autorisé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la mise à jour des messages"
 *     )
 * )
 */

    public function markMessageAsRead(Request $request){
        try {

            $request->validate([
                'messageIds' => 'required'
            ]);

            $ticketChat = TicketChat::whereId(TicketChatMessage::whereId($request->messageIds[0])->first()->ticket_chat_id)->first();

            foreach($request->messageIds as $messageId){

                $message = TicketChatMessage::find($messageId);

                if(!$message){
                    return (new ServiceController())->apiResponse(404, [],'Message non trouvé');
                }

                if($message->ticket_chat_id != $ticketChat->id){
                    return (new ServiceController())->apiResponse(404, [],'Tous les messages doivent provenir de la même conversation');
                }

                $supportTechniques = (new PermissionController())->getEmailsByPermissionName($this->permission);

                if(($message->receiver_id == null && (!in_array(Auth::user()->email, $supportTechniques))) || ($message->receiver_id =! null && $ticketChat->user_id != Auth::user()->id)){
                    return (new ServiceController())->apiResponse(404, [], "Vous n'êtes pas autorisé à marquer les messages de cette conversation comme lu");
                }

            }

            foreach($request->messageIds as $messageId){
                $message = TicketChatMessage::find($messageId);
                TicketChat::whereId($message->ticket_chat_id)->first()->update(['is_read' => 1]);
                $message-> is_read = 1;
                $message->save();
            }

            return (new ServiceController())->apiResponse(200, [],'Message marqué comme lu avec succès');

        } catch(Exception $e) {
             return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


    /**
 * @OA\Patch(
 *     path="/api/ticketChat/markMessageAsUnRead/{messageId}",
 *     tags={"Ticket Chat"},
 *     security={{"bearerAuth": {}}},
 *     summary="Marquer un message comme non lu",
 *     @OA\Parameter(
 *         name="messageId",
 *         in="path",
 *         required=true,
 *         description="ID du message",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message marqué comme non lu avec succès"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Message non trouvé ou non autorisé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la mise à jour du message"
 *     )
 * )
 */
    public function markMessageAsUnRead($messageId){
        try {

            $message = TicketChatMessage::find($messageId);

            if(!$message){
                return (new ServiceController())->apiResponse(404, [],'Message non trouvé');
            }

            $ticketChat = TicketChat::whereId($message->ticket_chat_id)->first();

            $supportTechniques = (new PermissionController())->getEmailsByPermissionName($this->permission);

            if(($message->receiver_id == null && (!in_array(Auth::user()->email, $supportTechniques))) || ($message->receiver_id =! null && $ticketChat->user_id != Auth::user()->id)){
                return (new ServiceController())->apiResponse(404, [], "Vous n'êtes pas autorisé à marquer ce message comme non lu");
            }

            $message->is_read = false;
            $message->save();
            TicketChat::whereId($message->chat_id)->first()->update(['is_read' => 0]);
            if(TicketChat::whereId($message->chat_id)->first()->model_type_concerned == "Support Information"){
                $message->done_by_id =  Auth::user()->id;
            }

            return (new ServiceController())->apiResponse(200, [],'Message marqué comme non lu avec succès');

        } catch(Exception $e) {
             return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    /**
 * @OA\Post(
 *     path="/api/ticketChat/closeTicket/{ticketChatId}",
 *     tags={"Ticket Chat"},
 *     security={{"bearerAuth": {}}},
 *     summary="Fermer un ticket de discussion",
 *     @OA\Parameter(
 *         name="ticketChatId",
 *         in="path",
 *         required=true,
 *         description="ID du ticket de discussion à fermer",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ticket de discussion fermé avec succès"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ticket de discussion non trouvé ou non autorisé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la fermeture du ticket"
 *     )
 * )
 */
    public function closeTicket($ticketChatId){
        try {

            $ticketChat = TicketChat::whereId($ticketChatId)->first();

            if(!$ticketChat){
             return (new ServiceController())->apiResponse(404, [], 'Ticket de discussion non trouvé');
            }

            $supportTechniques = (new PermissionController())->getEmailsByPermissionName($this->permission);

            if(!in_array(Auth::user()->email, $supportTechniques)){
                return (new ServiceController())->apiResponse(404, [], "Vous n'êtes pas autorisé à afficher la liste des préoccupations faites sur la plateforme");
            }

            if($ticketChat->is_deleted){
                return (new ServiceController())->apiResponse(404, [], 'Ticket de discussion supprimé');
            }

            if(!$ticketChat->is_open){
                return (new ServiceController())->apiResponse(404, [], 'Ticket de discussion déjà fermé');
            }

            $ticketChat->is_open = false;
            $ticketChat->is_closed_by = Auth::user()->id;
            $ticketChat->is_closed_on = now();
            $ticketChat->save();
            return (new ServiceController())->apiResponse(200, [], 'Ticket de discussion fermé');

        } catch(Exception $e) {
             return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

    /**
 * @OA\Post(
 *     path="/api/ticketChat/deleteChatTicketMessage/{messageId}",
 *     tags={"Ticket Chat"},
 *     security={{"bearerAuth": {}}},
 *     summary="Supprimer un message de ticket de discussion",
 *     @OA\Parameter(
 *         name="messageId",
 *         in="path",
 *         required=true,
 *         description="ID du message à supprimer",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message supprimé avec succès"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Message non trouvé ou non autorisé"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur lors de la suppression du message"
 *     )
 * )
 */
    public function deleteChatTicketMessage($ticketChatMessageId){
        $message = TicketChatMessage::find($ticketChatMessageId);

        if(!$message){
            return (new ServiceController())->apiResponse(404, [],'Message non trouvé');
        }

        if($message->sender_id != Auth::user()->id){
            return (new ServiceController())->apiResponse(404, [],"Vous n'avez pas le droit de supprimé ce message");
        }

        if($message->is_deleted ==1){
            return (new ServiceController())->apiResponse(404, [],'Message déjà supprimé');
        }

        if(TicketChat::whereId($message->ticket_chat_id)->first()->is_open ==0){
            return (new ServiceController())->apiResponse(404, [],'Ticket de discussion fermé. Vous ne pouvez pas supprimé ce message');
        }

        $message->is_deleted = 1;
        $message->save();
        return (new ServiceController())->apiResponse(200, [],'Message supprimé');
    }


}