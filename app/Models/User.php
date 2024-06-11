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
        'city',
        'address',
        'sexe',
        'postal_code',
        'file_profil',
        'partenaire_id'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'code'
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
    public function commission()
    {
        return $this->hasOne(Commission::class);
    }
    public function MoyenPayement()
    {
        return $this->hasMany(MoyenPayement::class);
    }

    public function user_right()
    {
        return $this->hasMany(User_right::class);
    }

    public function visites()
    {
        return $this->hasMany(UserVisiteHousing::class);
    }
    public function user_partenaire()
    {
        return $this->hasOne(user_partnaire::class);
    }
    public function verificationDocumentspartenaire()
    {
        return $this->hasMany(verification_document_partenaire::class);
    }
    public function Partenaire()
    {
        return $this->belongsTo(user_partnaire::class, 'partenaire_id', 'id');
    }

}
