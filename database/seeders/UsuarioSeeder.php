<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        
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

        // Crear Usuarios vinculados a esas personas
        DB::table('usuarios')->insert([
            [
                'id_persona' => $idJuan,
                'email' => 'juan@condominio.com',
                'pass' => 'passwordJuan123', // Texto plano según tu requerimiento actual
                'admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_persona' => $idMaria,
                'email' => 'maria@condominio.com',
                'pass' => 'passwordMaria456',
                'admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_persona' => $idPedro,
                'email' => 'pedro@condominio.com',
                'pass' => 'passwordPedro789',
                'admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}