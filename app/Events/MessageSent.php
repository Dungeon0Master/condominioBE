<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Recibimos el mensaje creado para poder enviarlo.
     */

    public $message;
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

   
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

  public function broadcastWith(): array
    {
       
        $user = $this->message->usuario;
        
        
        $nombreMostrar = $user->persona ? $user->persona->nombre : $user->name;

        return [
            'id' => $this->message->id, 
            'user_id' => $user->id,     
            'message' => $this->message->message,
            'user_name' => $nombreMostrar,
            'created_at' => $this->message->created_at,
        ];
    }
}