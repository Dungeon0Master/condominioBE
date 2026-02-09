<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla Departamentos
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id(); // Serial ID
            $table->string('depa'); // Ej: "A4"
            $table->boolean('moroso')->default(false);
            $table->string('codigo', 5)->nullable(); // Varchar(5)
            // No ponemos timestamps porque tu diagrama no los tiene
        });

        // 2. Tabla Intermedia per_dep (Relación Persona - Departamento)
        Schema::create('per_dep', function (Blueprint $table) {
            $table->id(); // Serial ID
            
            // Llaves foráneas
            $table->foreignId('id_persona')->constrained('personas')->onDelete('cascade');
            $table->foreignId('id_depa')->constrained('departamentos')->onDelete('cascade');
            
            // Campos extra de la relación
            $table->integer('id_rol')->default(1); // 1=Residente, 2=Dueño, etc.
            $table->boolean('residente')->default(true);
            $table->string('codigo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('per_dep');
        Schema::dropIfExists('departamentos');
    }
};