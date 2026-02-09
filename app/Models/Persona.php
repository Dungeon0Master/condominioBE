<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';
    public $timestamps = false;

    // Ajustado a tu diagrama: 'celular' es numeric, 'activo' es boolean
    protected $fillable = [
        'nombre', 'apellido_p', 'apellido_m', 'celular', 'activo'
    ];

    // Relación con Usuario
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona');
    }

    // Relación con Departamentos (Muchos a Muchos)
    public function departamentos()
    {
        // 'per_dep' es la tabla intermedia en tu diagrama
        return $this->belongsToMany(Departamento::class, 'per_dep', 'id_persona', 'id_depa')
                    ->withPivot('id_rol', 'residente', 'codigo');
    }
}