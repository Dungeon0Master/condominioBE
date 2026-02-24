<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Departamento;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Password;

class ResidenteController extends Controller
{
    public function index()
    {
        // Tu código de index está perfecto, lo dejamos igual
        $usuarios = Usuario::with(['persona.departamentos'])
            ->where('admin', false)
            ->latest('id')
            ->get();

        return $usuarios->map(function($user) {
            $depa = $user->persona->departamentos->first();
            return [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->persona->nombre . ' ' . $user->persona->apellido_p,
                'telefono' => $user->persona->celular,
                'condominio' => $depa ? $depa->depa : 'Sin asignar',
                'raw_persona' => $user->persona, 
                'raw_depa_codigo' => $depa ? $depa->depa : ''
            ];
        });
    }

    public function store(Request $request)
    {
        // Validar administrador
        if (! auth()->user()->admin) {
            return response()->json(['message' => 'No tienes permisos para crear usuarios.'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'apellido_p' => 'required',
            'celular' => 'required|numeric',
            'condominio' => 'required|exists:departamentos,depa',
        ]);

        // 2. Extraemos el usuario de la transacción
        $usuarioCreado = DB::transaction(function () use ($request) {
            
            $depa = Departamento::where('depa', $request->condominio)->firstOrFail();

            $persona = Persona::create([
                'nombre' => $request->nombre,
                'apellido_p' => $request->apellido_p,
                'apellido_m' => $request->apellido_m ?? '',
                'celular' => $request->celular,
                'activo' => true
            ]);

            $usuario = Usuario::create([
                'id_persona' => $persona->id,
                'email' => $request->email,
                'pass' => Hash::make($request->password), // Ahora sí funcionará
                'admin' => $request->admin ?? false,
            ]);

            $persona->departamentos()->attach($depa->id, [
                'id_rol' => 1, 
                'residente' => true,
                'codigo' => 'RES-' . $persona->id 
            ]);

            return $usuario; // Retornamos el usuario para usarlo fuera
        });

        // 2. Disparamos el correo de forma segura una vez que la DB guardó todo
        Password::sendResetLink(['email' => $usuarioCreado->email]);

    return response()->json(['message' => 'Residente creado. Se le ha enviado el correo de acceso.'], 201);
    }

    public function update(Request $request, $id)
    {
        // 3. Proteger también la actualización
        if (! auth()->user()->admin) {
            return response()->json(['message' => 'Acceso denegado. Solo administradores.'], 403);
        }

        $usuario = Usuario::with('persona')->findOrFail($id);
        
        $request->validate([
            'nombre' => 'required',
            'condominio' => 'required|exists:departamentos,depa',
           
        ]);
        
        return DB::transaction(function () use ($request, $usuario) {
             $usuario->persona->update([
                 'nombre' => $request->nombre,
                 'apellido_p' => $request->apellido_p,
                 'celular' => $request->celular
             ]);
             
             $usuario->update(['email' => $request->email]);
             
             $nuevoDepa = Departamento::where('depa', $request->condominio)->firstOrFail();
             
             $usuario->persona->departamentos()->sync([
                 $nuevoDepa->id => ['id_rol' => 1, 'residente' => true, 'codigo' => 'UPDATED']
             ]);
             
             return response()->json(['message' => 'Actualizado']);
        });
    }

    public function destroy($id) 
    {
        // Tu lógica actual de destroy está perfecta
        if (! auth()->user()->admin) {
            return response()->json(['message' => 'Acceso denegado. Solo los administradores pueden eliminar residentes.'], 403);
        }

        if (auth()->id() == $id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 400);
        }

        $usuario = Usuario::findOrFail($id);
        
        DB::transaction(function() use ($usuario) {
            $persona = $usuario->persona;
            if($persona) {
                $persona->departamentos()->detach(); 
                $persona->delete(); 
            }
            $usuario->delete(); 
        });

        return response()->json(['message' => 'Residente eliminado correctamente.']);
    }
}