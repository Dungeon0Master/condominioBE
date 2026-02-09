<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Crear Departamentos ---
        // Insertamos algunos departamentos comunes
        $depas = ['A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'C1', 'C4'];
        
        foreach ($depas as $nombreDepa) {
            DB::table('departamentos')->insertOrIgnore([
                'depa' => $nombreDepa,
                'moroso' => false,
                'codigo' => 'D-' . $nombreDepa
            ]);
        }

        // Recuperamos los IDs para usarlos abajo
        $idA1 = DB::table('departamentos')->where('depa', 'A1')->value('id');
        $idA4 = DB::table('departamentos')->where('depa', 'A4')->value('id');
        $idC4 = DB::table('departamentos')->where('depa', 'C4')->value('id');

        // --- 2. Crear Personas ---
        
        // Juan (Admin)
        $idJuan = DB::table('personas')->insertGetId([
            'nombre' => 'Juan',
            'apellido_p' => 'Pérez',
            'apellido_m' => 'García',
            'celular' => '5512345678', // Numeric en tu diagrama, string aquí está bien para insertar
            'activo' => true
        ]);

        // Maria (Residente)
        $idMaria = DB::table('personas')->insertGetId([
            'nombre' => 'Maria',
            'apellido_p' => 'López',
            'apellido_m' => 'Sánchez',
            'celular' => '5587654321',
            'activo' => true
        ]);

        // Pedro (Residente)
        $idPedro = DB::table('personas')->insertGetId([
            'nombre' => 'Pedro',
            'apellido_p' => 'Ramírez',
            'apellido_m' => 'Díaz',
            'celular' => '5555555555',
            'activo' => true
        ]);

        // --- 3. Crear Usuarios ---
        DB::table('usuarios')->insert([
            [
                'id_persona' => $idJuan,
                'email' => 'juan@condominio.com',
                'pass' => 'passwordJuan123', 
                'admin' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id_persona' => $idMaria,
                'email' => 'maria@condominio.com',
                'pass' => 'passwordMaria456',
                'admin' => false,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id_persona' => $idPedro,
                'email' => 'pedro@condominio.com',
                'pass' => 'passwordPedro789',
                'admin' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]
        ]);

        // --- 4. RELACIONAR EN per_dep (Lo nuevo) ---
        
        // Juan vive en A1
        DB::table('per_dep')->insert([
            'id_persona' => $idJuan,
            'id_depa' => $idA1,
            'id_rol' => 1,
            'residente' => true,
            'codigo' => 'RES-JUAN'
        ]);

        // Maria vive en C4 (Como en tu imagen de ejemplo)
        DB::table('per_dep')->insert([
            'id_persona' => $idMaria,
            'id_depa' => $idC4,
            'id_rol' => 1,
            'residente' => true,
            'codigo' => 'RES-MARIA'
        ]);

        // Pedro vive en A4
        DB::table('per_dep')->insert([
            'id_persona' => $idPedro,
            'id_depa' => $idA4,
            'id_rol' => 1,
            'residente' => true,
            'codigo' => 'RES-PEDRO'
        ]);
    }
}