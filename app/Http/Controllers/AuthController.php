<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password; 
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

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
        $deviceName = $request->header('User-Agent', 'auth_token');
        $token = $user->createToken($deviceName)->plainTextToken; 

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

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Contraseña guardada correctamente. Ya puedes iniciar sesión.']);
        }

        return response()->json(['message' => 'El enlace ha expirado o es inválido.'], 400);
    }

    // --- NUEVOS MÉTODOS PARA RECUPERACIÓN DE CONTRASEÑA ---

    // 1. Solicitar el código de 6 dígitos
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email'
        ]);

        // Generar un código numérico aleatorio de 6 dígitos
        $code = rand(100000, 999999);

        // Guardarlo en la base de datos (Hasheado por seguridad)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($code), 
                'created_at' => Carbon::now()
            ]
        );

        // Enviar el correo usando SMTP (Configurado en .env)
        Mail::raw("Tu código de recuperación para el condominio es: $code\n\nEste código expira en 15 minutos.", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Código de Recuperación de Contraseña');
        });

        return response()->json(['message' => 'Te hemos enviado un código de 6 dígitos a tu correo.']);
    }

    // 2. Validar el código y guardar la nueva contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
            'code' => 'required|numeric|digits:6',
            'password' => 'required|min:8|confirmed'
        ]);

        // Buscar el token asociado a ese correo
        $resetRequest = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // Validar que exista y coincida el código
        if (!$resetRequest || !Hash::check($request->code, $resetRequest->token)) {
            return response()->json(['message' => 'El código es incorrecto o no existe.'], 400);
        }

        // Validar que el código no haya expirado (15 minutos de validez)
        if (Carbon::now()->diffInMinutes($resetRequest->created_at) > 15) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'El código ha expirado. Solicita uno nuevo.'], 400);
        }

        // Actualizar contraseña del usuario
        $user = Usuario::where('email', $request->email)->first();
        $user->forceFill([
            'pass' => Hash::make($request->password),
            'email_verified_at' => now() 
        ])->save();

        // Cerrar sesión en todos los dispositivos
        $user->tokens()->delete();

        // Eliminar el código usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Contraseña actualizada. Todas tus sesiones han sido cerradas.']);
    }
}