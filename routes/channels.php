<?php
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', function ($usuario) {
    return $usuario !== null;
});