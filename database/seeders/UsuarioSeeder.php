<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // <-- IMPORTANTE: Agregar esto

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Crear Departamentos ---
        $depas = ['A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'C1', 'C4'];
        
        foreach ($depas as $nombreDepa) {
            DB::table('departamentos')->insertOrIgnore([
                'depa' => $nombreDepa,
                'moroso' => false,
                'codigo' => 'D-' . $nombreDepa
            ]);
        }

        $idA1 = DB::table('departamentos')->where('depa', 'A1')->value('id');
        $idA4 = DB::table('departamentos')->where('depa', 'A4')->value('id');
        $idC4 = DB::table('departamentos')->where('depa', 'C4')->value('id');

        // --- 2. Crear Personas ---
        $idJuan = DB::table('personas')->insertGetId([
            'nombre' => 'Juan',
            'apellido_p' => 'Pérez',
            'apellido_m' => 'García',
            'celular' => '5512345678',
            'activo' => true
        ]);

        $idMaria = DB::table('personas')->insertGetId([
            'nombre' => 'Maria',
            'apellido_p' => 'López',
            'apellido_m' => 'Sánchez',
            'celular' => '5587654321',
            'activo' => true
        ]);

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
                'email_verified_at' => now(),
                'pass' => Hash::make('passwordJuan123'), 
                'admin' => true, 
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id_persona' => $idMaria,
                'email' => 'maria@condominio.com',
                'email_verified_at' => now(),
                'pass' => Hash::make('passwordMaria456'), 
                'admin' => false,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id_persona' => $idPedro,
                'email' => 'pedro@condominio.com',
                'email_verified_at' => now(),
                'pass' => Hash::make('passwordPedro789'), 
                'admin' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]
        ]);

        // --- 4. RELACIONAR EN per_dep ---
        DB::table('per_dep')->insert([
            'id_persona' => $idJuan,
            'id_depa' => $idA1,
            'id_rol' => 1,
            'residente' => true,
            'codigo' => 'RES-JUAN'
        ]);

        DB::table('per_dep')->insert([
            'id_persona' => $idMaria,
            'id_depa' => $idC4,
            'id_rol' => 1,
            'residente' => true,
            'codigo' => 'RES-MARIA'
        ]);

        DB::table('per_dep')->insert([
            'id_persona' => $idPedro,
            'id_depa' => $idA4,
            'id_rol' => 1,
            'residente' => true,
            'codigo' => 'RES-PEDRO'
        ]);
    }
}