<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Housing;
use App\Models\Reservation;
use App\Models\ChatFile;
use App\Models\photo;
use App\Models\Right;
use App\Models\User;
use App\Models\User_right;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\TextUI\Output\NullPrinter;
use App\Services\FileService;


use function PHPUnit\Framework\isNull;

class ChatController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService=null)
    {
        $this->fileService = $fileService;
    }
    /**
     * @OA\Get(
     *     path="/api/chats/getChatsByModelType/{modelType}",
     *     summary="Récupère les discussions par type de modèle",
     *     tags={"Chats"},
     *  security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="modelType",
     *         in="path",
     *         required=true,
     *         description="Type du modèle (ex: Housing, Reservation)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des discussions par type de modèle",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="data", type="string", example="[]" ),
     *             @OA\Property(property="message", type="string", example="Liste des discussions groupées par type de modèle")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune donnée disponible",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Aucune donnée disponible")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Erreur serveur")
     *         )
     *     )
     * )
     */

     public function getChatsByModelType($modelType)
     {
         try {

             $userId = auth()->id();

             $models = (new AuditController)->getAllModels();
             $modelMappings = [];
            foreach ($models as $model) {
                $modelName = class_basename($model);
                $modelMappings[$modelName] = "App\Models\\$model";
            }

            if (!in_array($modelType, $models)) {
                return (new ServiceController())->apiResponse(404, [], "Le modèle $modelType spécifié n'existe pas.");
            }

             $chats = Chat::where('model_type_concerned', $modelType)
                          ->where(function($query) use ($userId) {
                              $query->where('sent_by', $userId)
                                    ->orWhere('sent_to', $userId);
                          })
                          ->get();

             if ($chats->isEmpty()) {
                return (new ServiceController())->apiResponse(404, [],'Aucune donnée disponible');
             }

            foreach($chats as $chat){

                $chat->send_by_name =User::whereId($chat->sent_by)->first() != null ?User::whereId($chat->sent_by)->first()->lastname." ". User::whereId($chat->sent_by)->first()->firstname:"Administrateur"   ;

                $chat->send_by_file_profil = User::whereId($chat->sent_by)->first() != null ?User::whereId($chat->sent_by)->first()->file_profil:"Administrateur";

                $chat->send_to_name =User::whereId($chat->sent_to)->first() != null ?User::whereId($chat->sent_to)->first()->lastname." ". User::whereId($chat->sent_to)->first()->firstname:"Administrateur"   ;

                $chat->send_to_file_profil = User::whereId($chat->sent_to)->first() != null ?User::whereId($chat->sent_to)->first()->file_profil:"Administrateur";

                $chat->housing_file =  photo::whereHousingId($chat->model_id)->whereIsCouverture(true)->exists() ? photo::whereHousingId($chat->model_id)->whereIsCouverture(true)->first()->path: photo::whereHousingId($chat->model_id)->first()->path;

                $chat->housing_name =  Housing::whereId($chat->model_id)->first()->name??"non renseigné";

                $chat->number_unread_message = ChatMessage::whereChatId($chat->id)->whereIsRead(0)->whereReceiverId(Auth::user()->id)->count();
            }

             return (new ServiceController())->apiResponse(200, $chats,'Liste des discussions groupées par type de modèle pour l\'utilisateur connecté');

         } catch (Exception $e) {
             return (new ServiceController())->apiResponse(500, [], $e->getMessage());
         }
     }



    /**
     * @OA\Get(
     *     path="/api/chats/getChatsByModelTypeAndId/{modelType}/{modelId}",
     *     summary="Récupère les discussions par type de modèle et ID de modèle",
     *     tags={"Chats"},
     *  security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="modelType",
     *         in="path",
     *         required=true,
     *         description="Type du modèle (ex: Housing, Reservation)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="modelId",
     *         in="path",
     *         required=true,
     *         description="ID du modèle",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des discussions par type de modèle et ID de modèle",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *               @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Liste des discussions groupées par type de modèle et concernant un modèle spécifique")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune donnée disponible",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Aucune donnée disponible")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=500),
     *               @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Erreur serveur")
     *         )
     *     )
     * )
     */

     public function getChatsByModelTypeAndId($modelType, $modelId)
     {
         try {

             $userId = auth()->id();
             $models = (new AuditController)->getAllModels();
             $modelMappings = [];
            foreach ($models as $model) {
                $modelName = class_basename($model);
                $modelMappings[$modelName] = "App\Models\\$model";
            }

            if (!in_array($modelType, $models)) {
                return (new ServiceController())->apiResponse(404, [], "Le modèle $modelType spécifié n'existe pas.");
            }

            // $modelMappings = [
            //     'Housing' => 'App\Models\Housing',
            //     'Reservation' => 'App\Models\Reservation',
            // ];

            if ($modelType && isset($modelMappings[$modelType]) && $modelType!= "Support Information") {
                $modelClass = $modelMappings[$modelType];
                if (!(new $modelClass())::find($modelId)) {
                    return (new ServiceController())->apiResponse(404, [], "$modelType non trouvé pour l'id $modelId");
                }
            }

             $chats = Chat::where('model_type_concerned', $modelType)
                          ->where('model_id', $modelId)
                          ->where(function($query) use ($userId) {
                              $query->where('sent_by', $userId)
                                    ->orWhere('sent_to', $userId);
                          })
                          ->get();

             if ($chats->isEmpty()) {
                return (new ServiceController())->apiResponse(404, $chats,'Aucune donnée disponible');
             }

             foreach($chats as $chat){
                $chat->number_unread_message = ChatMessage::whereChatId($chat->id)->whereIsRead(0)->whereReceiverId(Auth::user()->id)->count();
            }

             return (new ServiceController())->apiResponse(200,$chats,'Liste des discussions groupées par type de modèle et concernant un modèle spécifique pour l\'utilisateur connecté');

         } catch (Exception $e) {
             return (new ServiceController())->apiResponse(500, [], $e->getMessage());
         }
     }


    /**
 * @OA\Post(
 *     path="/api/chats/markMessageAsRead",
 *     summary="Marque plusieurs messages comme lus",
 *     tags={"Chats"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"messageIds"},
 *             @OA\Property(
 *                 property="messageIds",
 *                 type="array",
 *                 description="Tableau des IDs de messages à marquer comme lus",
 *                 @OA\Items(
 *                     type="integer",
 *                     example=123
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Messages marqués comme lus avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=200),
 *             @OA\Property(property="data", type="string", example="[]"),
 *             @OA\Property(property="message", type="string", example="Messages marqués comme lus avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Accès refusé pour certains messages",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=403),
 *             @OA\Property(property="data", type="string", example="[]"),
 *             @OA\Property(property="message", type="string", example="Vous n'avez pas le droit de marquer ce message comme lu")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Message non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=404),
 *             @OA\Property(property="data", type="string", example="[]"),
 *             @OA\Property(property="message", type="string", example="Message non trouvé")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="status_code", type="integer", example=500),
 *             @OA\Property(property="data", type="string", example="[]"),
 *             @OA\Property(property="message", type="string", example="Erreur serveur")
 *         )
 *     )
 * )
 */



    public function markMessageAsRead(Request $request){
        try {

            $request->validate([
                'messageIds' => 'required'
            ]);

            $chat = Chat::whereId(ChatMessage::whereId($request->messageIds[0])->first()->chat_id)->first();



            foreach($request->messageIds as $messageId){

                $message = ChatMessage::find($messageId);

                if(!$message){
                    return (new ServiceController())->apiResponse(404, [],'Message non trouvé');
                }

                if($message->chat_id != $chat->id){
                    return (new ServiceController())->apiResponse(404, [],'Tous les messages doivent provenir de la même conversation');
                }
    
                if($message->receiver_id != Auth::user()->id && Chat::whereId($message->chat_id)->first()->model_type_concerned!= "Support Information"){
                    return (new ServiceController())->apiResponse(404, [],'Vous n\'avez pas le droit de marquer ce message comme lu');
                }

            }

            foreach($request->messageIds as $messageId){
                $message = ChatMessage::find($messageId);
                Chat::whereId($message->chat_id)->first()->update(['is_read' => 1]);
                if(Chat::whereId($message->chat_id)->first()->model_type_concerned == "Support Information"){
                    $message->done_by_id =  Auth::user()->id;
                }
                $message-> is_read = 1;
                $message->save();
            }

            return (new ServiceController())->apiResponse(200, [],'Message marqué comme lu avec succès');

        } catch(Exception $e) {
             return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }


     


    /**
     * @OA\Post(
     *     path="/api/chats/markMessageAsUnRead/{messageId}",
     *     summary="Marque un message comme non lu",
     *     tags={"Chats"},
     *  security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="messageId",
     *         in="path",
     *         required=true,
     *         description="ID du message",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message marqué comme non lu avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *               @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Message marqué comme non lu avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=404),
     *               @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Message non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=500),
     *               @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Erreur serveur")
     *         )
     *     )
     * )
     */


    public function markMessageAsUnRead($messageId){
        try {

            $message = ChatMessage::find($messageId);

            if(!$message){
                return (new ServiceController())->apiResponse(404, [],'Message non trouvé');
            }
            if($message->receiver_id != Auth::user()->id && Chat::whereId($message->chat_id)->first()->model_type_concerned != "Support Information" ){
                return (new ServiceController())->apiResponse(404, [],'Vous n\'avez pas le droit de marquer ce message comme non lu');
            }
            $message->is_read = false;
            $message->save();
            Chat::whereId($message->chat_id)->first()->update(['is_read' => 0]);
            if(Chat::whereId($message->chat_id)->first()->model_type_concerned == "Support Information"){
                $message->done_by_id =  Auth::user()->id;
            }

            return (new ServiceController())->apiResponse(200, [],'Message marqué comme non lu avec succès');

        } catch(Exception $e) {
             return (new ServiceController())->apiResponse(500,[],$e->getMessage());
        }
    }

 /**
 * @OA\Post(
 *     path="/api/chats/createMessage/{recipientId}/{content}",
 *     summary="Create a new message",
 *     tags={"Chats"},
 *     security={{"bearerAuth": {}}},
 *      @OA\RequestBody(
 *     required=true,
 *     @OA\MediaType(
 *       mediaType="multipart/form-data",
 *       @OA\Schema(
 *         type="object",
 *          @OA\Property(
 *                     property="files[]",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary", description="Image de la catégorie (JPEG, PNG, JPG, GIF, taille max : 2048)")
 *                 ),
 *       )
 *     )
 *   ),
 *     @OA\Parameter(
 *         name="recipientId",
 *         in="path",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             description="The ID of the recipient"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="content",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             description="The content of the message"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="chatId",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             description="The ID of the chat (optional)"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="ModelId",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *             description="The ID of the model (optional)"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="ModelType",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             description="The type of the model (optional)"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=200
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Message créé avec succès"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Chat or model not found",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=404
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Conversation non trouvé"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=505,
 *         description="Sensitive information detected",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=505
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Sensitive information detected"
 *             )
 *         )
 *     )
 * )
 */


    public function createMessage(Request $request, $recipientId, $content)
    {
        DB::beginTransaction();
        try {

            $extensions = ['jpg','jpeg','png','gif','webp','bmp','svg','tiff','mp4','mov','avi','mkv','mpeg','webm'];


            if($request->file('files')){
                foreach ($request->file('files') as $file) {
                    $extension = $file->getClientOriginalExtension();
                    if (!in_array($extension, $extensions)) {
                        $allowedExtensions = implode(', ', $extensions);
                        return (new ServiceController())->apiResponse(404,[], "Les fichiers doivent avoir une des extensions suivantes : $allowedExtensions. Le fichier fourni a l'extension : $extension.");
                    }
                }
            }

            // return ($recipientId);
            $chatId = $request->query('chatId');
            $ModelId = $request->query('ModelId');
            $ModelType = $request->query('ModelType');
            $exist = new ChatMessage();
            $randomString = $this->generateRandomAlphaNumeric(7,$exist,'filecode');

            $sensitiferrors = $this->detectSensitiveInfo($content);

            if($sensitiferrors){
                return (new ServiceController())->apiResponse(505, [],$sensitiferrors);
            }

            // $modelMappings = [
            //     'Housing' => 'App\Models\Housing',
            //     'Reservation' => 'App\Models\Reservation',
            // ];

            if (!User::find($recipientId) && $ModelType!= "Support Information") {
                return (new ServiceController())->apiResponse(404, [], "Receveur non trouvé, vérifié l'id du receveur que vous envoyé");

            }

            

            if ($ModelType && $ModelType!= "Support Information") {

                $models = (new AuditController)->getAllModels();
                if (!in_array( $request->query('ModelType'), $models)) {
                    return (new ServiceController())->apiResponse(404, [], "Le modèle  {$request->query('ModelType')} spécifié n'existe pas.");
                }

                $modelMappings = [];
                foreach ($models as $model) {
                    $modelName = class_basename($model);
                    $modelMappings[$modelName] = "App\Models\\$model";
                }

                $modelClass = $modelMappings[$ModelType];
                if (!(new $modelClass())::find($ModelId)) {
                    return (new ServiceController())->apiResponse(404, [], "$ModelType non trouvé pour l'id $ModelId");
                }
            }else{
                return (new ServiceController())->apiResponse(404, [], "Model comportant le nom $ModelType non trouvé");
            }

            $senderId = Auth::user()->id;


            $existingChat = Chat::where('model_id', $ModelId)
                ->where('model_type_concerned', $ModelType)
                ->where(function($query) use ($senderId, $recipientId) {
                    $query->where('sent_to', $senderId)
                          ->where('sent_by', $recipientId)
                          ->orWhere(function($query) use ($senderId, $recipientId) {
                              $query->where('sent_to', $recipientId)
                                    ->where('sent_by', $senderId);
                          });
                })
                ->first();
            if ($existingChat) {
                // // Récupérer les informations des utilisateurs
                // $recipient = User::find($recipientId);
                // $sender = User::find($senderId);

                // // Message clair avec les noms des utilisateurs
                // $message = "Un chat entre {$sender->firstname} {$sender->lastname} et {$recipient->firstname} {$recipient->lastname} sur le sujet {$ModelType} ayant l'id {$ModelId}  existe déjà.";

                // return (new ServiceController())->apiResponse(404, [], $message);

                $chatId = $existingChat->id;

            }

            if ($chatId) {

                $chat = Chat::find($chatId);

                if (!$chat) {
                    return (new ServiceController())->apiResponse(404, [], "Conversation non trouvé");
                }
                   if($recipientId !=$chat->sent_to && $recipientId != $chat->sent_by)         {
                    // if($recipientId== auth()->id()){
                    //     $a = $chat->sent_to;
                    // }

                    // if($recipientId != auth()->id()){
                    //     $a = $chat->sent_by;
                    // }
                    // return $a;
                    // $a = $recipientId == auth()->id() ? $chat->sender_id : $chat->receiver_id;

                    return (new ServiceController())->apiResponse(404, [], "Mauvaise valeur pour le recepteur donné.");

                    }

                $message = new ChatMessage();
                $message->content = $content;
                $message->sender_id = auth()->id();
                $message->receiver_id =intval($recipientId)??null;
                $message->filecode = $randomString;
                $message->chat_id = intval($chatId);
                $message->save();

                $chat->last_message = $content;
                $chat->save();
            } else {
                $ModelId = $request->query('ModelId');
                $ModelType = $request->query('ModelType');

                if(!$ModelId && $ModelType!= "Support Information"){
                    return (new ServiceController())->apiResponse(404, [], "L'id du model est important pour la création d'une nouvelle conversation");
                }

                if(!$ModelType && $ModelType!= "Support Information"){
                    return (new ServiceController())->apiResponse(404, [], "Le type du model est important pour la création d'une nouvelle conversation");
                }
                $sentTo = $recipientId;
                $sentBy = auth()->id();
                
                $chat = new Chat();
                $message = new ChatMessage();
                $chat->sent_by = auth()->id();
                if($ModelType == "Support Information"){
                    $right = Right::where('name','admin')->first();
                    $adminUsers = User_right::where('right_id', $right->id)->first();
                    $chat->sent_to = $adminUsers->user_id;
                    $message->receiver_id = $adminUsers->user_id;
                }else{
                    $chat->sent_to = $recipientId;
                    $message->receiver_id = intval($recipientId);
                }
                $chat->model_type_concerned = $ModelType;
                $chat->model_id = intval($ModelId);
                $chat->save();

                $message->content = $content;
                $message->sender_id = auth()->id();
                $message->filecode = $randomString;
                $message->chat_id = $chat->id;
                $message->save();

                $chat->last_message = $content;
                $chat->save();
            }


            if($request->file('files')){
                $identity_profil_url = $this->fileService->uploadFiles($request->file('files'), 'image/imageChat', 'extensionImageVideo');;
                if ($identity_profil_url['fails']!=1) {
                    $locationFile = '';
                    $locationFile = $identity_profil_url['result'];
                    $referencecode = $randomString;
        
                    DB::table('chat_files')->insert([
                        'location' => $locationFile,
                        'referencecode' => $referencecode,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                }
            }

            DB::commit();

            if($chatId){

                if(Chat::whereId($chatId)->first()->model_type_concerned =="Support Information"){
                    $right = Right::where('name','admin')->first();
                    $adminUsers = User_right::where('right_id', $right->id)->get();
                    foreach ($adminUsers as $adminUser) {

                        $mailadmin = [
                            'title' => " Réponse d'un voyageur ",
                            "body" => "Réponse d'un voyageur"
                        ];

                    // (new NotificationController())->store($personToNotify,$mail['body'],$mail['title'],2);

                    }
                }else{
                    $mailreceiver = [
                        'title' => " Nouveau message ",
                         "body" => "Nouveau message !"
                    ];

                    (new NotificationController())->store(User::whereId($recipientId)->first()->email,$mailreceiver['body'],$mailreceiver['title'],0);
                }

            }else{

                if($ModelType == "Support Information"){

                    $right = Right::where('name','admin')->first();
                    $adminUsers = User_right::where('right_id', $right->id)->get();
                    foreach ($adminUsers as $adminUser) {

                        $mailadmin = [
                            'title' => " Nouvelle préoccupation d'un voyageur ",
                             "body" => "Nouvelle préoccupation d'un voyageur"
                        ];

                      }

                }else{

                    $mailreceiver = [
                        'title' => " Nouvelle conversation ",
                         "body" => "Vous venez de recevoir une nouvelle demande de discussion d'un voyageur concernant un(e) $ModelType"
                    ];

                    (new NotificationController())->store(User::whereId($recipientId)->first()->email,$mailreceiver['body'],$mailreceiver['title'],0);
                }
            }

            return (new ServiceController())->apiResponse(200,[],'Message créé avec succès');

            } catch(Exception $e) {
                DB::rollBack();
                return (new ServiceController())->apiResponse(500,[],$e->getMessage());
            }
    }

    private function generateRandomAlphaNumeric($length,$class,$colonne) {
        $bytes = random_bytes(ceil($length * 3 / 4));
        $randomS =  substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $length);

        $exist = $class->where($colonne,$randomS)->first();
        while($exist){
            $randomS =  substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $length);
        }

        return $randomS;
    }

   
    function detectSensitiveInfo($message)
    {
        $errors = [];

        // Vérifier les numéros de téléphone (exemple: 123-456-7890, +33123456789)
        $phonePattern = '/\+?\d[\d\s-]{8,}\d/';
        if (preg_match($phonePattern, $message)) {
            $errors[] = "Le message contient un numéro de téléphone, ce qui n'est pas autorisé.";
        }

        // Vérifier les adresses email
        $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        if (preg_match($emailPattern, $message)) {
            $errors[] = "Le message contient une adresse email, ce qui n'est pas autorisé.";
        }

        // Vérifier les montants en monnaie (exemple: $100, 100€, 1000.00)
        $moneyPattern = '/\b\d+(?:[\.,]\d{1,2})?\s?(?:€|\$|dollars?|euros?|usd|eur)\b/i';
        if (preg_match($moneyPattern, $message)) {
            $errors[] = "Le message contient un montant d'argent (numérique), ce qui n'est pas autorisé.";
        }

        // Vérifier les montants écrits en lettres (exemple: cent, mille, etc.)
        $moneyWordsPattern = '/\b(zero|un|deux|trois|quatre|cinq|six|sept|huit|neuf|dix|onze|douze|treize|quatorze|quinze|seize|vingt|trente|quarante|cinquante|soixante|soixante-dix|quatre-vingts|quatre-vingt-dix|cent|mille|million|milliard)\b/i';
        if (preg_match($moneyWordsPattern, $message)) {
            $errors[] = "Le message contient un montant d'argent écrit en lettres, ce qui n'est pas autorisé.";
        }

        // Vérifier les coordonnées physiques (exemple: 123 rue Exemple, 75000 Paris)
        $addressPattern = '/\b\d{1,5}\s\w+\s\w+/i';
        if (preg_match($addressPattern, $message)) {
            $errors[] = "Le message contient une adresse physique, ce qui n'est pas autorisé.";
        }

        // Vérifier les noms d'utilisateur ou identifiants de réseaux sociaux (ex: @username)
        $socialMediaPattern = '/@[a-zA-Z0-9._]{2,}/';
        if (preg_match($socialMediaPattern, $message)) {
            $errors[] = "Le message contient un identifiant de réseau social, ce qui n'est pas autorisé.";
        }

        // Vérifier les URL (exemple: www.exemple.com, http://exemple.com)
        $urlPattern = '/\b((http|https):\/\/|www\.)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        if (preg_match($urlPattern, $message)) {
            $errors[] = "Le message contient un lien URL, ce qui n'est pas autorisé.";
        }

        // Vérifier les numéros de carte bancaire (exemple: 1234 5678 9876 5432)
        $creditCardPattern = '/\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/';
        if (preg_match($creditCardPattern, $message)) {
            $errors[] = "Le message contient un numéro de carte bancaire, ce qui n'est pas autorisé.";
        }

        // Vérifier les adresses IP (exemple: 192.168.0.1)
        $ipPattern = '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/';
        if (preg_match($ipPattern, $message)) {
            $errors[] = "Le message contient une adresse IP, ce qui n'est pas autorisé.";
        }

        // Vérifier les dates (pour éviter de fixer des rendez-vous en dehors de la plateforme)
        $datePattern = '/\b\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4}\b/';
        if (preg_match($datePattern, $message)) {
            $errors[] = "Le message contient une date, ce qui n'est pas autorisé.";
        }

        return $errors;
    }


   /**
 * @OA\Get(
 *     path="/api/chats/getMessagesByChatId/{chatId}",
 *     summary="Get messages by chat ID",
 *     tags={"Chats"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="chatId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             description="The ID of the chat"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Messages retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=200
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(
 *                         property="id",
 *                         type="integer",
 *                         example=1
 *                     ),
 *                     @OA\Property(
 *                         property="message",
 *                         type="string",
 *                         example="Hello World!"
 *                     )
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Messages récupérés avec succès"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Chat not found",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=404
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Conversation non trouvée"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="status_code",
 *                 type="integer",
 *                 example=403
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Vous n'êtes pas autorisé à accéder à cette conversation"
 *             )
 *         )
 *     )
 * )
 */


    public function getMessagesByChatId($chatId)
    {
        try {
            $userId = auth()->id();


            $chat = Chat::find($chatId);


            if (!$chat) {
                return (new ServiceController())->apiResponse(404,[],'Conversation non trouvée');
            }


            if ($chat->sent_by !== $userId && $chat->sent_to !== $userId) {
                return (new ServiceController())->apiResponse(403,[],'Vous n\'êtes pas autorisé à accéder à cette conversation');
            }


            $messages = ChatMessage::with('file')->where('chat_id', $chatId)->where('is_deleted_by_receiver',false)->where('is_deleted_by_sender',false)->get();

            $data = [
                'messages' => $messages,
                'userAuth' => Auth::user()->id
            ];


            return (new ServiceController())->apiResponse(200,$data,'Messages récupérés avec succès');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }






}

//   try {
//            return (new ServiceController())->apiResponse(500,[],$e->getMessage());
//         }
//         } catch(Exception $e) {
//              return (new ServiceController())->apiResponse(500,[],$e->getMessage());
//         }
