<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\Request;
use GuzzleHttp\Client;


class TestController extends Controller
{
    public function verifyEmail($email)
    {
        $client = new Client();

        try {
            $response = $client->get('https://api.hunter.io/v2/email-verifier', [
                'query' => [
                    'email' => $email,
                    'api_key' => $this->hunterApiKey(),
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if ($data['data']['result'] === 'deliverable' && Setting::first()->app_mode == 'PRODUCTION') {
                // return response()->json(['message' => 'Email is deliverable'], 200);
                return 'deliverable';
            } else {
                // return response()->json(['message' => 'Email is not deliverable'], 400);
                return 'undeliverable';
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to verify email'], 500);
        }
    }


    public function hunterApiKey(){
        return 'b03ce18847f32555e238af58e5bc1f2a851912a8';
    }

   

   
}


