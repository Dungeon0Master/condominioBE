<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla personas
            // constrained('personas') asegura que el ID exista en la otra tabla
            $table->foreignId('id_persona')->constrained('personas')->onDelete('cascade');
            
            // Agregamos email 
            $table->string('email')->unique();
            
            //  campo de contraseña 
            $table->string('pass');
            
            //  campo de administrador
            $table->boolean('admin')->default(false);

            // Timestamps para saber cuándo se creó el usuario 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};