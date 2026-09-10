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
        Schema::create('animales', function (Blueprint $table) {
            $table->id('id_animal');
            $table->string('numero_arete', 50)->unique();
            $table->foreignId('raza_id')->constrained('razas', 'raza_id')->onDelete('restrict');
            $table->enum('sexo', ['Macho', 'Hembra']);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('estado', 50)->default('Activo');
            $table->foreignId('potrero_id')->constrained('potreros', 'potrero_id')->onDelete('restrict');
            $table->timestamps();
            $table->index('numero_arete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animales');
    }
};
