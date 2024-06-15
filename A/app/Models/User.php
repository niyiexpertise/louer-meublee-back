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
        'user_id',
        'housing_id',
        'date_of_reservation',
        'date_of_starting',
        'date_of_end',
        'number_of_adult',
        'number_of_child',
        'number_of_domestical_animal',
        'number_of_baby',
        'message_to_hote',
        'code_pays',
        'telephone_traveler',
        'heure_arrivee_max',
        'heure_arrivee_min',
        'is_tranche_paiement',
        'montant_total',
        'valeur_payee',
        'is_confirmed_hote',
        'is_integration',
        'is_rejected_traveler',
        'is_rejected_hote',
        'photo',
        'valeur_reduction_hote',
        'valeur_promotion_hote',
        'valeur_reduction_code_promo',
        'valeur_reduction_staturp',
        'montant_charge',
        'montant_housing',
        'montant_a_paye'
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
