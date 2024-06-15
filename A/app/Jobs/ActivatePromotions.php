<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use App\Models\promotion;

class ActivatePromotions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {

    }

    public function handle()
    {
        $currentDate = Carbon::now();

        $promotions = promotion::where('is_encours', false)
            ->where('is_deleted', false)
            ->where('is_blocked', false)
            ->where('date_debut', '<=', $currentDate)
            ->where('date_fin', '>=', $currentDate)
            ->get();

        foreach ($promotions as $promotion) {
            $promotion->is_encours = true;
            $promotion->save();
        }
    }
}

