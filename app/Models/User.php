<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
}
