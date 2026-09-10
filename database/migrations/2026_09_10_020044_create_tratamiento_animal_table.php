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
        Schema::create('tratamiento_animal', function (Blueprint $table) {
            $table->id('tratamiento_animal_id');
            $table->foreignId('id_animal')->constrained('animales', 'id_animal')->onDelete('restrict');
            $table->foreignId('tratamiento_id')->constrained('tratamientos', 'tratamiento_id')->onDelete('restrict');
            $table->dateTime('fecha_aplicacion');
            $table->decimal('dosis_ml', 6, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tratamiento_animal');
    }
};
