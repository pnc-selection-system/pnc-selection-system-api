<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        // 'remember_token', // you can remove if not using
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Automatically hash the password when it's set.
     */
    public function setPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
        }
    }

    // Relationship
    public function role()
    {
        return $this->belongsTo(Role::class); // assuming you have Role model
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role_id' => $this->role_id,   // Optional: add role in token
        ];
    }
}
