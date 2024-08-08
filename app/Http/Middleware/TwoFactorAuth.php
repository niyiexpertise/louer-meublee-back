<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->is_double_authentification==false ) {
            
            return response()->json([
                'status_code' =>403,
                'data' =>[],
                'message' => "Double authentification requise.Passez par la procédure normale. Authentifiez vous d'abord et renseignez le code ensuite "
            ], 200);
        }

        return $next($request);
    }
}