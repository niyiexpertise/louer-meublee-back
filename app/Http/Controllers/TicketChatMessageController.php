<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\TicketChat;
use App\Models\TicketChatFile;
use App\Models\TicketChatMessage;
use App\Models\User;
use App\Services\FileService;
use Exception;
use Illuminate\Http\Request;

class TicketChatMessageController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService=null)
    {
        $this->fileService = $fileService;
    }
    public function createTicketChatMessage($ticketChatId, $content, $senderId, $files)
    {

       $ticketChat = TicketChat::whereId($ticketChatId)->first();

       if(!$ticketChat){
        return (new ServiceController())->apiResponse(404, [], 'Ticket de discussion non trouvé');
       }

       if($senderId == $ticketChat->user_id){
            $receiverId = null;
            $senderType = 'utilisateur';

            $mailreceiver = [
                'title' => "Nouveau ",
                "body" => "Vous venez de recevoir un nouveau message d'un administrateur concernant votre préoccupation à propos de '' $ticketChat->title '' "
            ];
       }else{
            $receiverId = $ticketChat->user_id;
            $senderType = 'administrateur';
       }

        $message = new TicketChatMessage();
        $message->content = $content;
        $message->ticket_chat_id = $ticketChatId;
        $message->sender_id = $senderId;
        $message->receiver_id = $receiverId;
        $message->sender_type = $senderType;
        $message->save();

        if($files){
            $identity_profil_url = $this->fileService->uploadFiles($files, 'image/imageChatTicket', 'extensionImageVideo');;
                if ($identity_profil_url['fails']!=1) {
                    $locationFile = '';
                    $locationFile = $identity_profil_url['result'];

                    $file = new TicketChatFile();
                    $file->location = $locationFile;
                    $file->extension = $file->getClientOriginalExtension();
                    $file->ticket_chat_message_id = $message->id;
                    $file->save();
                }
        }

        if($senderType == 'administrateur'){
            $mailreceiver = [
                'title' => " Support d'assistance ",
                "body" => "Vous venez de recevoir un nouveau message d'un administrateur concernant votre préoccupation à propos de '' $ticketChat->title '' "
            ];

            (new NotificationController())->store(User::whereId($receiverId)->first()->email,$mailreceiver['body'],$mailreceiver['title'],0);
        }else{
            if(TicketChatMessage::whereTicketChatId($ticketChat->id)->count() > 1){
                $mailreceiver = [
                    'title' => " Nouveau message",
                    "body" => "Vous venez de recevoir un nouveau message d'un utilisateur concernant sa préoccupation à propos de '' $ticketChat->title '' "
                ];

                $personToNotify = (new PermissionController())->getEmailsByPermissionName('Managelogement.validateCategoriesHousing');
                (new NotificationController())->store($personToNotify,$mailreceiver['body'],$mailreceiver['title'],2);
            }else{
                $mailreceiver = [
                    'title' => " Nouvelle préoccupation",
                    "body" => "Vous venez de recevoir une nouvelle préoccupation intitulée '' $ticketChat->title ''. Veuillez y répondre afin de satisfaire l'utilisateur"
                ];

                $personToNotify = (new PermissionController())->getEmailsByPermissionName('Managelogement.validateCategoriesHousing');
                (new NotificationController())->store($personToNotify,$mailreceiver['body'],$mailreceiver['title'],2);
            }
        }


    }
}
