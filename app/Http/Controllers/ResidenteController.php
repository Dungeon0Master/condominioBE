<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResidenteController extends Controller
{
    public function index()
    {
        // Traemos usuarios con su persona y LOS DEPARTAMENTOS de esa persona
        $usuarios = Usuario::with(['persona.departamentos'])
            ->where('admin', false) // O lo que uses para filtrar residentes
            ->latest('id')
            ->get();

        // Formateamos para el frontend (React espera "condominio" como string plano)
        return $usuarios->map(function($user) {
            // Obtenemos el primer departamento (asumiendo que vive en uno principal)
            $depa = $user->persona->departamentos->first();
            
            return [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->persona->nombre . ' ' . $user->persona->apellido_p,
                'telefono' => $user->persona->celular,
                'condominio' => $depa ? $depa->depa : 'Sin asignar', // Ej: "A4"
                
                // Datos crudos por si los necesitas en el modal de edición
                'raw_persona' => $user->persona, 
                'raw_depa_codigo' => $depa ? $depa->depa : ''
            ];
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'apellido_p' => 'required',
            'celular' => 'required|numeric',
            'email' => 'required|email|unique:usuarios,email',
            'condominio' => 'required|exists:departamentos,depa', // Validamos que "A4" exista en la tabla departamentos campo 'depa'
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Buscar el Departamento (ID) basado en el nombre "A4"
            $depa = Departamento::where('depa', $request->condominio)->firstOrFail();

            // 2. Crear Persona
            $persona = Persona::create([
                'nombre' => $request->nombre,
                'apellido_p' => $request->apellido_p,
                'apellido_m' => $request->apellido_m ?? '',
                'celular' => $request->celular,
                'activo' => true
            ]);

            // 3. Crear Usuario
            $usuario = Usuario::create([
                'id_persona' => $persona->id,
                'email' => $request->email,
                'pass' => $request->pass ?? '12345678', // Ojo con tu campo 'pass' (sin encriptar según modelo anterior, o usa Hash::make si cambiaste)
                'admin' => false
            ]);

            // 4. Llenar la tabla intermedia 'per_dep'
            // attach recibe el ID del departamento y un array con los campos extra de la tabla pivote
            $persona->departamentos()->attach($depa->id, [
                'id_rol' => 1, // Asume 1 = Residente (ajusta según tu tabla roles)
                'residente' => true,
                'codigo' => 'RES-' . $persona->id // O algún código lógico
            ]);

            return response()->json(['message' => 'Residente creado correctamente']);
        });
    }

    public function update(Request $request, $id)
    {
        // Lógica similar para update... se busca usuario, se actualiza persona
        // y se usa $persona->departamentos()->sync(...) para cambiar el depa.
        $usuario = Usuario::with('persona')->findOrFail($id);
        
        $request->validate([
            'nombre' => 'required',
            'condominio' => 'required|exists:departamentos,depa',
             // ... resto validaciones
        ]);
        
        return DB::transaction(function () use ($request, $usuario) {
             // 1. Actualizar Persona
             $usuario->persona->update([
                 'nombre' => $request->nombre,
                 'apellido_p' => $request->apellido_p,
                 'celular' => $request->celular
             ]);
             
             // 2. Actualizar Usuario
             $usuario->update(['email' => $request->email]);
             
             // 3. Actualizar Relación Departamento
             $nuevoDepa = Departamento::where('depa', $request->condominio)->firstOrFail();
             
             // Sync actualiza la relación en per_dep eliminando la anterior
             $usuario->persona->departamentos()->sync([
                 $nuevoDepa->id => ['id_rol' => 1, 'residente' => true, 'codigo' => 'UPDATED']
             ]);
             
             return response()->json(['message' => 'Actualizado']);
        });
    }
    
    public function destroy($id) {
        $usuario = Usuario::findOrFail($id);
        // Borramos en cascada manual (Usuario -> Persona -> Pivot se borra solo o manual)
        DB::transaction(function() use ($usuario) {
            $persona = $usuario->persona;
            $usuario->delete(); // Borra usuario
            if($persona) {
                $persona->departamentos()->detach(); // Borra relaciones en per_dep
                $persona->delete(); // Borra persona
            }
        });
        return response()->json(['message' => 'Eliminado']);
    }
}