<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ResidenteController;
use Illuminate\Support\Facades\Broadcast;

// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);

Broadcast::routes(['middleware' => ['auth:sanctum']]);


// Rutas Protegidas (Solo con Token)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Ruta para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // RUTAS DEL CHAT (
    Route::get('/messages', [ChatController::class, 'index']); // Obtener historial
    Route::post('/messages', [ChatController::class, 'store']); // Enviar mensaje

    Route::get('/residentes', [ResidenteController::class, 'index']);
Route::post('/residentes', [ResidenteController::class, 'store']);
Route::put('/residentes/{id}', [ResidenteController::class, 'update']);
Route::delete('/residentes/{id}', [ResidenteController::class, 'destroy']);
    
    // Ruta de prueba para saber quién soy
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});