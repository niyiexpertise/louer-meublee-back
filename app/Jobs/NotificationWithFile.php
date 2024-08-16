<?php

namespace App\Jobs;

use App\Http\Controllers\NotificationController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationWithFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

     protected $name;
     protected $object;
     protected $email;
     protected $attachedFiles;
    public function __construct($email, $name,$object,$attachedFiles)
    {
        $this->email = $email;
        $this->name = $name;
        $this->object = $object;
        $this->attachedFiles = $attachedFiles;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new NotificationController())->storeAndSendFileEmail($this->email,$this->name,$this->object,$this->attachedFiles);
    }
}
