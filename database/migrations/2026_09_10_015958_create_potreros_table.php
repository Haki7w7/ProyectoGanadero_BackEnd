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
        Schema::create('potreros', function (Blueprint $table) {
            $table->id('potrero_id');
            $table->string('nombre', 100);
            $table->decimal('hectareas_de_extension',8,2);
            $table->integer('capacidad_maxima');
            $table->string('estado_pasto', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('potreros');
    }
};
