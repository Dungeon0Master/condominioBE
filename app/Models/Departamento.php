<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';
    public $timestamps = false; 

    protected $fillable = ['depa', 'moroso', 'codigo'];

    // Relación muchos a muchos con Persona a través de per_dep
    public function personas()
    {
        return $this->belongsToMany(Persona::class, 'per_dep', 'id_depa', 'id_persona')
                    ->withPivot('id_rol', 'residente', 'codigo');
    }
}