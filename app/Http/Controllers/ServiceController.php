<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function apiResponse($status,$data,$message=''){

        if(env('MODE') == 'PRODUCTION' && $status == 500){
            $message = "An error occured";
        }

        return response()->json([
            'status_code' => $status,
            'data' =>$data,
            'message' => $message,
        ], 200);
    }
}
