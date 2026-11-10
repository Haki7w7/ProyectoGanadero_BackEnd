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
        Schema::create('puntos_ronda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ronda_id')->constrained('rondas')->onDelete('cascade');
            $table->foreignId('potrero_id')->constrained('potreros', 'potrero_id')->onDelete('cascade');
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->string('estado_potrero', 50); // bueno, regular, requiere_atencion
            $table->text('observacion')->nullable();
            $table->timestamp('registrado_en')->useCurrent();
            $table->timestamps();

            $table->unique(['ronda_id', 'potrero_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_ronda');
    }
};

