<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Importante para el envío de correo
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

// Implementamos MustVerifyEmail
class Usuario extends Authenticatable implements MustVerifyEmail 
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $table = 'usuarios';

    protected $fillable = [
        'id_persona',
        'email',
        'pass',
        'admin',
    ];

    protected $hidden = [
        'pass',
    ];

    public function getAuthPassword()
    {
        return $this->pass;
    }

    // Casteamos el verified_at
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'usuario_id');
    }
}