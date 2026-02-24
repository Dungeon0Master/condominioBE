<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password; 
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    // Login actualizado con Hash
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = Usuario::where('email', $request->email)->with('persona')->first();

        // Usamos Hash::check para comparar el texto plano con el hash guardado en 'pass'
        if (! $user || ! Hash::check($request->password, $user->pass)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        //  Bloquear acceso si no ha verificado el correo
        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Verifica tu correo electrónico antes de iniciar sesión.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function setNewPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed', // Exige confirmación
        ]);

        // El 'broker' verifica que el token sea válido y no haya expirado
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Aquí actualizamos tu campo personalizado 'pass'
                $user->forceFill([
                    'pass' => Hash::make($password),
                    'email_verified_at' => now(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Contraseña guardada correctamente. Ya puedes iniciar sesión.']);
        }

        return response()->json(['message' => 'El enlace ha expirado o es inválido.'], 400);
    }
}