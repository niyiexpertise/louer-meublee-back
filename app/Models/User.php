<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lastname',
        'firstname',
        'password',
        'telephone',
        'code_pays',
        'email',
        'country',
        'file_profil',
        'city',
        'address',
        'sexe',
        'postal_code',
        'is_admin',
        'is_traveller',
        'is_hote'
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function user_role()
    {
        return $this->hasMany(User_role::class);
    }
    

    public function user_language()
    {
        return $this->hasMany(User_language::class);
                   
    }
    public function reservation()
    {
        return $this->hasMany(Reservation::class);
    }
    public function note()
    {
        return $this->hasMany(Note::class);
    }
    public function user_preference()
    {
        return $this->hasMany(User_preference::class);
                   
    }
    public function housing()
    {
        return $this->hasMany(Housing::class);
    }
    public function review()
    {
        return $this->hasMany(Review::class);
    }

    public function verificationDocuments()
    {
        return $this->hasMany(verification_document::class);
    }

    public function favorites()
   {
    return $this->hasMany(Favoris::class);
   }

   public function portfeuille()
    {
        return $this->hasOne(Portfeuille::class);
    }
    public function MoyenPayement()
    {
        return $this->hasMany(MoyenPayement::class);
    }

}
