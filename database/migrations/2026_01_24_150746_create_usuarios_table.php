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
            
            $table->foreignId('id_persona')->constrained('personas')->onDelete('cascade');
            
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); //  confirmación de correo
            
            $table->string('pass'); // Aquí guardaremos el hash,
            
            $table->boolean('admin')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};