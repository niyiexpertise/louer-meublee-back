<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationEmail;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Housing;
use App\Models\Reservation;
use App\Models\Right;
use App\Models\User;
use App\Models\User_right;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\TextUI\Output\NullPrinter;

use function PHPUnit\Framework\isNull;

class ChatController extends Controller
{

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

             return (new ServiceController())->apiResponse(200,$chats,'Liste des discussions groupées par type de modèle et concernant un modèle spécifique pour l\'utilisateur connecté');

         } catch (Exception $e) {
             return (new ServiceController())->apiResponse(500, [], $e->getMessage());
         }
     }
     

    /**
     * @OA\Post(
     *     path="/api/chats/markMessageAsRead/{messageId}",
     *     summary="Marque un message comme lu",
     *     tags={"Chats"},
     *  security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="messageId",
     *         in="path",
     *         required=true,
     *         description="ID du message",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message marqué comme lu avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *               @OA\Property(property="data", type="string", example="[]"),
     *             @OA\Property(property="message", type="string", example="Message marqué comme lu avec succès")
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


    public function markMessageAsRead($messageId){
        try {

            $message = ChatMessage::find($messageId);


            if(!$message){
                return (new ServiceController())->apiResponse(404, [],'Message non trouvé');
            }
            
            if($message->receiver_id != Auth::user()->id && Chat::whereId($message->chat_id)->first()->model_type_concerned!= "Support Information"){
                return (new ServiceController())->apiResponse(403, [],'Vous n\'avez pas le droit de marquer ce message comme lu');
            }
            $message->is_read = true;
           
            Chat::whereId($message->chat_id)->first()->update(['is_read' => 1]);

            if(Chat::whereId($message->chat_id)->first()->model_type_concerned == "Support Information"){
                $message->done_by_id =  Auth::user()->id;
            }

            $message->save();

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
                return (new ServiceController())->apiResponse(403, [],'Vous n\'avez pas le droit de marquer ce message comme non lu');
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

            if ($chatId) {

                $chat = Chat::find($chatId);

                if (!$chat) {
                    return (new ServiceController())->apiResponse(404, [], "Conversation non trouvé");
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
                $this->uploadFiles($request, $randomString,'ChatFile');
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
                    dispatch( new SendRegistrationEmail($adminUser->user->email, $mailadmin['body'], $mailadmin['title'], 0));
                    }
                }else{
                    $mailreceiver = [
                        'title' => " Nouveau message ",
                         "body" => "Nouveau message !"
                    ];
                    dispatch( new SendRegistrationEmail(User::whereId($recipientId)->first()->email, $mailreceiver['body'], $mailreceiver['title'], 0));
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
                    dispatch( new SendRegistrationEmail($adminUser->user->email, $mailadmin['body'], $mailadmin['title'], 2));
                      }

                }else{

                    $mailreceiver = [
                        'title' => " Nouvelle conversation ",
                         "body" => "Vous venez de recevoir une nouvelle demande de discussion d'un voyageur concernant un(e) $ModelType"
                    ];
                    dispatch( new SendRegistrationEmail(User::whereId($recipientId)->first()->email, $mailreceiver['body'], $mailreceiver['title'], 2));
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

    private function uploadFiles(Request $request, $randomString,$location){
        foreach($request->file('files') as $photo){
             $this->storeFile($photo, $randomString, $location);
        }
    }

    private function storeFile( $photo, $randomString, $location){
        try {

            $db = DB::connection()->getPdo();

            $size = filesize($photo);
            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');
            $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->move(public_path("image/$location"), $photoName);
            $photoUrl = url("/image/$location/" . $photoName);
            $type = $photo->getClientOriginalExtension();
            $locationFile = $photoUrl;
            $referencecode = $randomString;
            $filename = md5(uniqid()) . '.' . $type;
            $q = "INSERT INTO chat_files (filename, type, location,  referencecode,created_at,updated_at) VALUES (?,?,?,?,?,?)";
            $stmt = $db->prepare($q);
            $stmt->bindParam(1, $filename);
            $stmt->bindParam(2, $type);
            $stmt->bindParam(3, $locationFile);
            $stmt->bindParam(4,  $referencecode);
            $stmt->bindParam(5,  $created_at);
            $stmt->bindParam(6,  $updated_at);
            $stmt->execute();

        } catch (Exception $e) {
           return response()->json([
            'error' => $e->getMessage()
           ]);
        }
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

            return (new ServiceController())->apiResponse(200, $messages,'Messages récupérés avec succès');
        } catch (Exception $e) {
            return (new ServiceController())->apiResponse(500, [], $e->getMessage());
        }
    }


    



}

//   try {
//             return response()->json([
//                 'status_code' => 200,
//                 'data' =>[],
//                 'message' => 'notification created successffully'
//             ]);
//         } catch(Exception $e) {
//              return (new ServiceController())->apiResponse(500,[],$e->getMessage());
//         }