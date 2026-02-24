<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ResidenteController;
use Illuminate\Support\Facades\Broadcast;

// Rutas Públicas
// Rutas Públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/set-password', [AuthController::class, 'setNewPassword']);


Broadcast::routes(['middleware' => ['auth:sanctum']]);

// --- Rutas Protegidas (Requieren Token) ---
Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    

    // Rutas del Chat
    Route::get('/messages', [ChatController::class, 'index']);
    Route::post('/messages', [ChatController::class, 'store']);

    // Rutas de Residentes
    Route::get('/residentes', [ResidenteController::class, 'index']);
    Route::post('/residentes', [ResidenteController::class, 'store']);
    Route::put('/residentes/{id}', [ResidenteController::class, 'update']);
    Route::delete('/residentes/{id}', [ResidenteController::class, 'destroy']);
    
    // Verificar usuario actual
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});