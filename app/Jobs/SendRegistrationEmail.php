<?php

namespace App\Jobs;

use App\Http\Controllers\NotificationController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRegistrationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $name;
    protected $object;
    protected $email;
    protected $is_send_by_mail;
    public function __construct($email, $name,$object,$is_send_by_mail)
    {
        $this->email = $email;
        $this->name = $name;
        $this->object = $object;
        $this->is_send_by_mail = $is_send_by_mail;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // $user = User::find($this->userId);

            // if (!$user) {
            //     return 'User not found for ID: ' . $this->userId;
            //     ;
            // }
            (new NotificationController())->store($this->email,$this->name,$this->object,$this->is_send_by_mail);
            // return response()->json("ok");
        } catch (\Exception $e) {
            throw $e;
        }
        
    }
}
