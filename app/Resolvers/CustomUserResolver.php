<?php
namespace App\Resolvers;

use OwenIt\Auditing\Contracts\UserResolver;
use Illuminate\Support\Facades\Auth;

class CustomUserResolver implements UserResolver
{
    /**
     * {@inheritdoc}
     */
    public static function resolve()
    {
        // Retourner l'objet utilisateur actuellement authentifié ou null
        return Auth::user();
    }
}