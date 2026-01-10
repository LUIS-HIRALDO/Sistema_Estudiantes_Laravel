<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->string('telefono')->nullable();
            $table->date('fecha_nacimiento');
            $table->string('direccion')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->default('M');
            $table->string('nombre_padre')->nullable();
            $table->string('nombre_madre')->nullable();
            $table->string('telefono_emergencia')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo', 'Graduado'])->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
