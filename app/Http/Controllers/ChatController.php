<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    // Obtener todos los mensajes 
    public function index()
    {
        // Traemos los mensajes con la información del usuario que los envió
       return Message::with('usuario.persona')->latest()->take(50)->get()->reverse()->values();
    }

    // Guardar un nuevo mensaje y emitir el evento
    public function store(Request $request)
    {
        // Validamos que venga texto
        $request->validate([
            'message' => 'required|string',
        ]);

        //  Crear el mensaje en BD asociado al usuario autenticado
        $message = $request->user()->messages()->create([
            'message' => $request->message,
        ]);

        // Disparar el evento para Reverb, Esto envía la señal en tiempo real a los otros conectados
        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }
}
