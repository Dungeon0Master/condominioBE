<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $table = 'usuarios';

    // 2. Definimos los campos que se pueden escribir (mass assignment)
    protected $fillable = [
        'id_persona',
        'email',
        'pass',
        'admin',
    ];

    // 3. Ocultamos la contraseña para que no salga en las respuestas JSON
    protected $hidden = [
        'pass',
    ];

    
    public function getAuthPassword()
    {
        return $this->pass;
    }


    // Relación con Persona (para obtener Nombre y Apellido en el chat)
    public function persona()
    {
        // 'id_persona' es la llave foránea en esta tabla usuarios
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    // Relación con Mensajes (para que $usuario->messages funcione)
    public function messages()
    {
        return $this->hasMany(Message::class, 'usuario_id');
    }
}