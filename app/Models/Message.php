<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Message extends Model
{
    use HasFactory;
    protected $fillable = [
       'usuario_id',
        'message',
    ];

   public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
