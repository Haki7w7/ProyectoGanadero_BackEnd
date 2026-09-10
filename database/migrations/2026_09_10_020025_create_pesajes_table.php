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
        Schema::create('pesajes', function (Blueprint $table) {
            $table->id('pesaje_id');
            $table->foreignId('id_animal')->constrained('animales', 'id_animal')->onDelete('cascade');
            $table->decimal('peso_kg', 6, 2);
            $table->dateTime('fecha_pesaje');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['id_animal', 'fecha_pesaje']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesajes');
    }
};
